<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;

class HearingCollector extends BaseCollector
{
    public function collect(Model $model): array
    {
        return [
            'hearing' => [
                'attributes' => $this->getAttributes($model),
                'references' => [
                    'matter_id' => $model->matter_id,
                    'lawyer_id' => $model->lawyer_id,
                ],
            ],
        ];
    }

    public function getRootLabel(Model $model): string
    {
        $date = $model->date ? $model->date->format('Y-m-d') : 'N/A';
        $court = $model->court ?? 'Unknown Court';
        return "Hearing: {$court} ({$date})";
    }
}

