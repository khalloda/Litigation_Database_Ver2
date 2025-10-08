<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\Client;
use App\Models\DeletionBundle;
use App\Models\DeletionBundleItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeletionBundleService
{
    /**
     * Create a deletion bundle for a root entity.
     *
     * @param Model $root The root model (Client or Case)
     * @param string|null $reason Optional deletion reason
     * @return string Bundle UUID
     */
    public function createBundle(Model $root, ?string $reason = null): string
    {
        return DB::transaction(function () use ($root, $reason) {
            $rootType = class_basename($root);
            $rootLabel = $this->getRootLabel($root, $rootType);
            
            // Collect the entire graph before deletion
            $snapshot = $this->collectSnapshot($root, $rootType);
            $filesData = $this->collectFiles($snapshot);
            $cascadeCount = $this->countCascadeItems($snapshot);
            
            // Create the bundle
            $bundle = DeletionBundle::create([
                'root_type' => $rootType,
                'root_id' => $root->id,
                'root_label' => $rootLabel,
                'snapshot_json' => $snapshot,
                'files_json' => $filesData,
                'cascade_count' => $cascadeCount,
                'deleted_by' => auth()->id() ?? 1, // Fallback to system user
                'reason' => $reason,
                'status' => 'trashed',
            ]);
            
            // Create individual items for detailed tracking
            $this->createBundleItems($bundle, $snapshot);
            
            Log::info("Deletion bundle created", [
                'bundle_id' => $bundle->id,
                'root_type' => $rootType,
                'root_id' => $root->id,
                'cascade_count' => $cascadeCount,
            ]);
            
            return $bundle->id;
        });
    }

    /**
     * Restore a deletion bundle.
     *
     * @param string $bundleId Bundle UUID
     * @param array $options Restore options
     * @return array Restore report
     */
    public function restoreBundle(string $bundleId, array $options = []): array
    {
        $bundle = DeletionBundle::findOrFail($bundleId);
        
        if (!$bundle->isTrashed()) {
            throw new \RuntimeException("Bundle {$bundleId} is not in trashed status");
        }
        
        $dryRun = $options['dry_run'] ?? false;
        $conflictStrategy = $options['resolve_conflicts'] ?? 'skip'; // skip|overwrite|new_copy
        
        return DB::transaction(function () use ($bundle, $dryRun, $conflictStrategy) {
            $report = [
                'bundle_id' => $bundle->id,
                'root_type' => $bundle->root_type,
                'root_label' => $bundle->root_label,
                'dry_run' => $dryRun,
                'conflict_strategy' => $conflictStrategy,
                'restored' => [],
                'skipped' => [],
                'conflicts' => [],
                'errors' => [],
            ];
            
            $snapshot = $bundle->snapshot_json;
            $idMappings = []; // Old ID => New ID mappings
            
            // Restore in dependency order
            $restorationOrder = $this->getRestorationOrder($bundle->root_type);
            
            foreach ($restorationOrder as $entityType => $config) {
                $items = $snapshot[$entityType] ?? [];
                
                foreach ($items as $item) {
                    try {
                        $result = $this->restoreItem(
                            $item,
                            $config['model'],
                            $conflictStrategy,
                            $idMappings,
                            $dryRun
                        );
                        
                        if ($result['status'] === 'restored') {
                            $report['restored'][] = $result;
                            if (isset($result['old_id']) && isset($result['new_id'])) {
                                $idMappings[$config['model']][$result['old_id']] = $result['new_id'];
                            }
                        } elseif ($result['status'] === 'skipped') {
                            $report['skipped'][] = $result;
                        } elseif ($result['status'] === 'conflict') {
                            $report['conflicts'][] = $result;
                        }
                    } catch (\Exception $e) {
                        $report['errors'][] = [
                            'entity' => $entityType,
                            'item_id' => $item['attributes']['id'] ?? null,
                            'error' => $e->getMessage(),
                        ];
                    }
                }
            }
            
            // Update bundle status if not dry run
            if (!$dryRun) {
                $bundle->update([
                    'status' => 'restored',
                    'restored_at' => now(),
                    'restore_notes' => json_encode($report),
                ]);
                
                Log::info("Deletion bundle restored", [
                    'bundle_id' => $bundle->id,
                    'restored_count' => count($report['restored']),
                ]);
            }
            
            return $report;
        });
    }

    /**
     * Purge a deletion bundle.
     *
     * @param string $bundleId Bundle UUID
     * @return void
     */
    public function purgeBundle(string $bundleId): void
    {
        $bundle = DeletionBundle::findOrFail($bundleId);
        
        DB::transaction(function () use ($bundle) {
            // Optionally delete quarantined files (if implemented)
            $this->purgeFiles($bundle);
            
            // Mark as purged
            $bundle->update(['status' => 'purged']);
            
            // Or permanently delete
            // $bundle->delete();
            
            Log::info("Deletion bundle purged", [
                'bundle_id' => $bundle->id,
                'root_type' => $bundle->root_type,
            ]);
        });
    }

    /**
     * Get the label for a root entity.
     */
    protected function getRootLabel(Model $root, string $type): string
    {
        if ($type === 'Client') {
            return $root->client_name_ar ?? $root->client_name_en ?? "Client #{$root->id}";
        } elseif ($type === 'Case' || $type === 'CaseModel') {
            return $root->matter_name_ar ?? $root->matter_name_en ?? "Case #{$root->id}";
        }
        
        return "{$type} #{$root->id}";
    }

    /**
     * Collect snapshot of the entire entity graph.
     */
    protected function collectSnapshot(Model $root, string $rootType): array
    {
        $snapshot = [];
        
        if ($rootType === 'Client') {
            $client = $root;
            $snapshot['client'] = ['attributes' => $client->getAttributes()];
            
            // Load all related entities
            $snapshot['cases'] = $client->cases()->get()->map(fn($c) => [
                'attributes' => $c->getAttributes()
            ])->toArray();
            
            $snapshot['contacts'] = $client->contacts()->get()->map(fn($c) => [
                'attributes' => $c->getAttributes()
            ])->toArray();
            
            $snapshot['engagement_letters'] = $client->engagementLetters()->get()->map(fn($e) => [
                'attributes' => $e->getAttributes()
            ])->toArray();
            
            $snapshot['power_of_attorneys'] = $client->powerOfAttorneys()->get()->map(fn($p) => [
                'attributes' => $p->getAttributes()
            ])->toArray();
            
            // Collect nested data from cases
            $caseIds = collect($snapshot['cases'])->pluck('attributes.id')->toArray();
            
            if (!empty($caseIds)) {
                $snapshot['hearings'] = DB::table('hearings')
                    ->whereIn('matter_id', $caseIds)
                    ->get()
                    ->map(fn($h) => ['attributes' => (array) $h])
                    ->toArray();
                
                $snapshot['admin_tasks'] = DB::table('admin_tasks')
                    ->whereIn('matter_id', $caseIds)
                    ->get()
                    ->map(fn($t) => ['attributes' => (array) $t])
                    ->toArray();
                
                $taskIds = collect($snapshot['admin_tasks'])->pluck('attributes.id')->toArray();
                
                if (!empty($taskIds)) {
                    $snapshot['admin_subtasks'] = DB::table('admin_subtasks')
                        ->whereIn('task_id', $taskIds)
                        ->get()
                        ->map(fn($s) => ['attributes' => (array) $s])
                        ->toArray();
                }
            }
            
            // Documents
            $snapshot['documents'] = $client->documents()->get()->map(function($d) {
                return [
                    'attributes' => $d->getAttributes(),
                    'file' => $this->getFileInfo($d),
                ];
            })->toArray();
            
        } elseif ($rootType === 'Case' || $rootType === 'CaseModel') {
            $case = $root;
            $snapshot['case'] = ['attributes' => $case->getAttributes()];
            
            // Hearings
            $snapshot['hearings'] = DB::table('hearings')
                ->where('matter_id', $case->id)
                ->get()
                ->map(fn($h) => ['attributes' => (array) $h])
                ->toArray();
            
            // Admin tasks and subtasks
            $snapshot['admin_tasks'] = DB::table('admin_tasks')
                ->where('matter_id', $case->id)
                ->get()
                ->map(fn($t) => ['attributes' => (array) $t])
                ->toArray();
            
            $taskIds = collect($snapshot['admin_tasks'])->pluck('attributes.id')->toArray();
            
            if (!empty($taskIds)) {
                $snapshot['admin_subtasks'] = DB::table('admin_subtasks')
                    ->whereIn('task_id', $taskIds)
                    ->get()
                    ->map(fn($s) => ['attributes' => (array) $s])
                    ->toArray();
            }
            
            // Documents
            $snapshot['documents'] = DB::table('client_documents')
                ->where('matter_id', $case->id)
                ->get()
                ->map(function($d) {
                    return [
                        'attributes' => (array) $d,
                        'file' => [], // File info would go here
                    ];
                })->toArray();
        }
        
        return $snapshot;
    }

    /**
     * Collect file information from snapshot.
     */
    protected function collectFiles(array $snapshot): array
    {
        $files = [];
        
        foreach ($snapshot['documents'] ?? [] as $doc) {
            if (!empty($doc['file'])) {
                $files[] = $doc['file'];
            }
        }
        
        return $files;
    }

    /**
     * Get file information for a document.
     */
    protected function getFileInfo($document): array
    {
        // This would be implemented based on your file storage structure
        return [];
    }

    /**
     * Count total items in snapshot.
     */
    protected function countCascadeItems(array $snapshot): int
    {
        $count = 0;
        
        foreach ($snapshot as $key => $items) {
            if ($key === 'client' || $key === 'case') {
                $count += 1;
            } elseif (is_array($items)) {
                $count += count($items);
            }
        }
        
        return $count;
    }

    /**
     * Create individual bundle items for tracking.
     */
    protected function createBundleItems(DeletionBundle $bundle, array $snapshot): void
    {
        foreach ($snapshot as $entityType => $items) {
            if ($entityType === 'client' || $entityType === 'case') {
                DeletionBundleItem::create([
                    'bundle_id' => $bundle->id,
                    'model' => ucfirst($entityType),
                    'model_id' => $items['attributes']['id'],
                    'payload_json' => $items['attributes'],
                ]);
            } elseif (is_array($items)) {
                foreach ($items as $item) {
                    DeletionBundleItem::create([
                        'bundle_id' => $bundle->id,
                        'model' => $this->getModelName($entityType),
                        'model_id' => $item['attributes']['id'] ?? null,
                        'payload_json' => $item['attributes'] ?? $item,
                    ]);
                }
            }
        }
    }

    /**
     * Get model name from entity type.
     */
    protected function getModelName(string $entityType): string
    {
        $map = [
            'cases' => 'CaseModel',
            'hearings' => 'Hearing',
            'contacts' => 'Contact',
            'engagement_letters' => 'EngagementLetter',
            'power_of_attorneys' => 'PowerOfAttorney',
            'admin_tasks' => 'AdminTask',
            'admin_subtasks' => 'AdminSubtask',
            'documents' => 'ClientDocument',
        ];
        
        return $map[$entityType] ?? ucfirst(Str::singular($entityType));
    }

    /**
     * Get restoration order (dependency order).
     */
    protected function getRestorationOrder(string $rootType): array
    {
        if ($rootType === 'Client') {
            return [
                'client' => ['model' => 'Client'],
                'engagement_letters' => ['model' => 'EngagementLetter'],
                'contacts' => ['model' => 'Contact'],
                'power_of_attorneys' => ['model' => 'PowerOfAttorney'],
                'cases' => ['model' => 'CaseModel'],
                'hearings' => ['model' => 'Hearing'],
                'admin_tasks' => ['model' => 'AdminTask'],
                'admin_subtasks' => ['model' => 'AdminSubtask'],
                'documents' => ['model' => 'ClientDocument'],
            ];
        } else {
            return [
                'case' => ['model' => 'CaseModel'],
                'hearings' => ['model' => 'Hearing'],
                'admin_tasks' => ['model' => 'AdminTask'],
                'admin_subtasks' => ['model' => 'AdminSubtask'],
                'documents' => ['model' => 'ClientDocument'],
            ];
        }
    }

    /**
     * Restore a single item.
     */
    protected function restoreItem(
        array $item,
        string $modelClass,
        string $conflictStrategy,
        array $idMappings,
        bool $dryRun
    ): array {
        $attributes = $item['attributes'] ?? $item;
        $modelFullClass = "App\\Models\\{$modelClass}";
        
        // Check if item exists
        $existingId = $attributes['id'] ?? null;
        $exists = $existingId && $modelFullClass::withTrashed()->find($existingId);
        
        if ($exists) {
            // Handle conflict
            if ($conflictStrategy === 'skip') {
                return [
                    'status' => 'skipped',
                    'model' => $modelClass,
                    'id' => $existingId,
                    'reason' => 'exists',
                ];
            } elseif ($conflictStrategy === 'overwrite') {
                if (!$dryRun) {
                    $exists->update($attributes);
                    if ($exists->trashed()) {
                        $exists->restore();
                    }
                }
                return [
                    'status' => 'restored',
                    'model' => $modelClass,
                    'id' => $existingId,
                    'action' => 'overwritten',
                ];
            }
        }
        
        // Restore or create new
        if (!$dryRun) {
            $newId = $modelFullClass::create($attributes)->id;
        } else {
            $newId = $existingId;
        }
        
        return [
            'status' => 'restored',
            'model' => $modelClass,
            'old_id' => $existingId,
            'new_id' => $newId ?? $existingId,
            'action' => $exists ? 'updated' : 'created',
        ];
    }

    /**
     * Purge files associated with bundle.
     */
    protected function purgeFiles(DeletionBundle $bundle): void
    {
        // Implementation would delete quarantined files if applicable
        // For now, this is a placeholder
    }
}

