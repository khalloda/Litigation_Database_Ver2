<?php

namespace App\Services\Import;

use App\Models\ImportProfile;
use App\Models\ImportChoice;
use App\Support\Import\HeaderHasher;

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
                if (!$colChoices) { $misses++; continue; }

                $normalized = mb_strtolower(trim($raw), 'UTF-8');
                $match = $colChoices->first(function ($c) use ($normalized) {
                    return $c->normalized_value === $normalized;
                });

                if (!$match) { $misses++; continue; }

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
}


