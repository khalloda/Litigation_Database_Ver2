<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LawyerCollector extends BaseCollector
{
    public function collect(Model $model): array
    {
        $snapshot = [
            'lawyer' => [
                'attributes' => $this->getAttributes($model),
            ],
        ];

        // Capture assignment references (for information only, not full restoration)
        $snapshot['assignments'] = [
            'hearings' => DB::table('hearings')
                ->where('lawyer_id', $model->id)
                ->pluck('id')
                ->toArray(),
            'admin_tasks' => DB::table('admin_tasks')
                ->where('lawyer_id', $model->id)
                ->pluck('id')
                ->toArray(),
            'admin_subtasks' => DB::table('admin_subtasks')
                ->where('lawyer_id', $model->id)
                ->pluck('id')
                ->toArray(),
        ];

        return $snapshot;
    }

    public function getRootLabel(Model $model): string
    {
        return $model->lawyer_name_ar ?? $model->lawyer_name_en ?? "Lawyer #{$model->id}";
    }
}

