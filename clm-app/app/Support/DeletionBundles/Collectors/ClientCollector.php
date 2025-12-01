<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClientCollector extends BaseCollector
{
    public function collect(Model $model): array
    {
        $snapshot = [];
        
        // Root client
        $snapshot['client'] = ['attributes' => $this->getAttributes($model)];
        
        // Cases (with full cascade)
        $cases = $model->cases()->get();
        $snapshot['cases'] = $cases->map(fn($c) => ['attributes' => $c->getAttributes()])->toArray();
        $caseIds = $cases->pluck('id')->toArray();
        
        // Contacts
        $snapshot['contacts'] = $this->collectRelated($model->contacts());
        
        // Engagement Letters
        $snapshot['engagement_letters'] = $this->collectRelated($model->engagementLetters());
        
        // Power of Attorneys
        $snapshot['power_of_attorneys'] = $this->collectRelated($model->powerOfAttorneys());
        
        // Collect nested data from cases
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
            
            $taskIds = collect($snapshot['admin_tasks'])->pluck('attributes.id')->filter()->toArray();
            
            if (!empty($taskIds)) {
                $snapshot['admin_subtasks'] = DB::table('admin_subtasks')
                    ->whereIn('task_id', $taskIds)
                    ->get()
                    ->map(fn($s) => ['attributes' => (array) $s])
                    ->toArray();
            }
        }
        
        // Documents
        $snapshot['documents'] = $model->documents()->get()->map(function($d) {
            return [
                'attributes' => $d->getAttributes(),
                'file' => [], // File descriptor would go here
            ];
        })->toArray();
        
        return $snapshot;
    }

    public function getRootLabel(Model $model): string
    {
        return $model->client_name_ar ?? $model->client_name_en ?? "Client #{$model->id}";
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

