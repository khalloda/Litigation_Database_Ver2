<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdminTaskCollector extends BaseCollector
{
    public function collect(Model $model): array
    {
        // Collect the task itself
        $snapshot = [
            'admin_task' => [
                'attributes' => $this->getAttributes($model),
                'references' => [
                    'matter_id' => $model->matter_id,
                    'lawyer_id' => $model->lawyer_id,
                ],
            ],
        ];

        // Collect all subtasks (cascade behavior)
        $subtasks = DB::table('admin_subtasks')
            ->where('task_id', $model->id)
            ->get()
            ->map(fn($s) => ['attributes' => (array) $s])
            ->toArray();

        if (!empty($subtasks)) {
            $snapshot['admin_subtasks'] = $subtasks;
        }

        return $snapshot;
    }

    public function getRootLabel(Model $model): string
    {
        $work = $model->required_work ?? 'Admin Task';
        return substr($work, 0, 50) . (strlen($work) > 50 ? '...' : '');
    }
}

