<?php

namespace App\Services\Import;

use App\Models\ImportProfile;
use App\Models\ImportChoice;
use App\Support\Import\HeaderHasher;
use App\Models\ImportSession;
use App\Support\TextNormalizer;

class ImportProfileService
{
    public function selectProfile(string $tableName, array $headers): ?ImportProfile
    {
        $hash = HeaderHasher::hash($headers);
        return ImportProfile::query()
            ->active()
            ->forTable($tableName)
            ->where(function ($q) use ($hash) {
                $q->where('header_hash', $hash)
                    ->orWhereNull('header_hash');
            })
            ->orderByRaw('header_hash IS NULL') // prefer exact match
            ->first();
    }

    /**
     * Apply profile choices to incoming rows. Returns summary.
     * @return array{hits:int,misses:int,applied:int,decisions:array}
     */
    public function applyProfile(ImportProfile $profile, array &$rows, array $columns): array
    {
        $hits = $misses = $applied = 0;

        // Preload choices grouped by column for quick lookup
        $choices = ImportChoice::query()
            ->active()
            ->where('profile_id', $profile->id)
            ->where('table_name', $profile->table_name)
            ->get()
            ->groupBy('column');

        foreach ($rows as $i => &$row) {
            foreach ($columns as $sourceCol => $targetCol) {
                if (!$targetCol || !isset($row[$sourceCol])) continue;
                $raw = (string) $row[$sourceCol];
                if ($raw === '' || is_numeric($raw)) continue; // ID > Name precedence
                $colChoices = $choices->get($targetCol);
                if (!$colChoices) {
                    $misses++;
                    continue;
                }

                $normalized = mb_strtolower(trim($raw), 'UTF-8');
                $match = $colChoices->first(function ($c) use ($normalized) {
                    return $c->normalized_value === $normalized;
                });

                if (!$match) {
                    $misses++;
                    continue;
                }

                $hits++;
                // Minimal application; detailed actions integrated later in pipeline
                if ($match->action === 'match' && $match->entity_id && $match->entity_model) {
                    // Place resolved value back into row as numeric id if target is *_id
                    if (str_ends_with($targetCol, '_id')) {
                        $row[$sourceCol] = (string) $match->entity_id;
                        $applied++;
                    }
                } elseif ($match->action === 'ignore') {
                    $row[$sourceCol] = '';
                    $applied++;
                }
                // alias/capacity will be handled at import stage using metadata
            }
        }

        return [
            'hits' => $hits,
            'misses' => $misses,
            'applied' => $applied,
            'decisions' => [],
        ];
    }

    /**
     * Persist resolved preflight choices from a session into an import profile.
     * - Creates/selects a profile by table + header hash (or null)
     * - Upserts ImportChoice by (profile_id, table_name, column, normalized_value)
     */
    public function persistChoicesFromSession(
        ImportSession $session,
        array $headers,
        bool $saveAsProfile,
        bool $rememberChoices,
        ?string $profileName = null
    ): ?ImportProfile {
        if (!$rememberChoices && !$saveAsProfile) {
            return null;
        }

        $hash = !empty($headers) ? HeaderHasher::hash($headers) : null;

        // Select or create a profile
        $profile = null;
        if ($saveAsProfile && $profileName) {
            $profile = ImportProfile::firstOrCreate(
                ['name' => $profileName],
                [
                    'table_name' => $session->table_name,
                    'header_hash' => $hash,
                    'is_active' => true,
                    'created_by' => $session->user_id,
                ]
            );
        } else {
            // Use existing linked profile if any, or fallback unnamed/null-hash profile
            if ($session->profile_id) {
                $profile = ImportProfile::find($session->profile_id);
            }
            if (!$profile) {
                $profile = ImportProfile::firstOrCreate(
                    [
                        'name' => 'Auto: ' . $session->table_name,
                    ],
                    [
                        'table_name' => $session->table_name,
                        'header_hash' => $hash,
                        'is_active' => true,
                        'created_by' => $session->user_id,
                    ]
                );
            }
        }

        if (!$profile) {
            return null;
        }

        // Map columns to models when action=match
        $columnModelMap = [
            'court_id' => \App\Models\Court::class,
            'client_capacity_id' => \App\Models\OptionValue::class,
            'opponent_capacity_id' => \App\Models\OptionValue::class,
            'opponent_id' => \App\Models\Opponent::class,
            'matter_partner_id' => \App\Models\Lawyer::class,
            'circuit_name_id' => \App\Models\OptionValue::class,
            'circuit_secretary' => \App\Models\Lawyer::class,
        ];

        $normalizer = app(TextNormalizer::class);
        $errors = is_array($session->preflight_errors) ? $session->preflight_errors : [];

        foreach ($errors as $e) {
            if (!is_array($e) || empty($e['resolved']) || empty($e['column'])) {
                continue;
            }
            $column = (string) $e['column'];
            $raw = (string) ($e['value'] ?? '');
            $normalized = $normalizer->normalize($raw);
            $entityId = $e['resolved_id'] ?? null;

            if ($normalized === '' || !$entityId) {
                continue;
            }

            $action = 'match';
            $entityModel = $columnModelMap[$column] ?? null;

            // Capacity-specific hint
            $metadata = [];
            if (str_contains($column, 'capacity')) {
                $action = 'capacity';
                $metadata['capacity_id'] = (int) $entityId;
            }

            // Upsert choice
            ImportChoice::updateOrCreate(
                [
                    'profile_id' => $profile->id,
                    'table_name' => $session->table_name,
                    'column' => $column,
                    'normalized_value' => $normalized,
                ],
                [
                    'raw_value' => $raw !== '' ? $raw : null,
                    'action' => $action,
                    'entity_model' => $entityModel,
                    'entity_id' => $entityId,
                    'metadata_json' => $metadata ?: null,
                    'is_active' => true,
                    'updated_by' => $session->user_id,
                ]
            );
        }

        // Link profile to session for traceability
        if ($session->profile_id !== $profile->id) {
            $session->update(['profile_id' => $profile->id]);
        }

        return $profile;
    }
}
