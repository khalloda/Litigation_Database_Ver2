<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;

class AdminSubtaskCollector extends BaseCollector
{
    public function collect(Model $model): array
    {
        return [
            'admin_subtask' => [
                'attributes' => $this->getAttributes($model),
                'references' => [
                    'task_id' => $model->task_id,
                    'lawyer_id' => $model->lawyer_id,
                ],
            ],
        ];
    }

    public function getRootLabel(Model $model): string
    {
        $result = $model->result ?? 'Subtask';
        return "Subtask: " . substr($result, 0, 40) . (strlen($result) > 40 ? '...' : '');
    }
}

