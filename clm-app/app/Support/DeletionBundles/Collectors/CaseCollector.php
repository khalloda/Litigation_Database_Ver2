<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CaseCollector extends BaseCollector
{
    public function collect(Model $model): array
    {
        $snapshot = [];
        
        // Root case
        $snapshot['case'] = [
            'attributes' => $this->getAttributes($model),
            'references' => [
                'client_id' => $model->client_id,
                'contract_id' => $model->contract_id,
            ],
        ];
        
        // Hearings
        $snapshot['hearings'] = DB::table('hearings')
            ->where('matter_id', $model->id)
            ->get()
            ->map(fn($h) => ['attributes' => (array) $h])
            ->toArray();
        
        // Admin tasks
        $snapshot['admin_tasks'] = DB::table('admin_tasks')
            ->where('matter_id', $model->id)
            ->get()
            ->map(fn($t) => ['attributes' => (array) $t])
            ->toArray();
        
        $taskIds = collect($snapshot['admin_tasks'])->pluck('attributes.id')->filter()->toArray();
        
        if (!empty($taskIds)) {
            $snapshot['admin_subtasks'] = DB::table('admin_subtasks')
                ->whereIn('task_id', $taskIds)
                ->get()
                ->map(fn($s) => ['attributes' => (array) $s])
                ->toArray();
        }
        
        // Documents (case-specific)
        $snapshot['documents'] = DB::table('client_documents')
            ->where('matter_id', $model->id)
            ->get()
            ->map(function($d) {
                return [
                    'attributes' => (array) $d,
                    'file' => [], // File info would go here
                ];
            })->toArray();
        
        return $snapshot;
    }

    public function getRootLabel(Model $model): string
    {
        return $model->matter_name_ar ?? $model->matter_name_en ?? "Case #{$model->id}";
    }

    public function getFileDescriptors(array $snapshot): array
    {
        $files = [];
        
        foreach ($snapshot['documents'] ?? [] as $doc) {
            if (!empty($doc['file'])) {
                $files[] = $doc['file'];
            }
        }
        
        return $files;
    }
}

