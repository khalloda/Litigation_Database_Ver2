<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;

class EngagementLetterCollector extends BaseCollector
{
    public function collect(Model $model): array
    {
        return [
            'engagement_letter' => [
                'attributes' => $this->getAttributes($model),
                'references' => [
                    'client_id' => $model->client_id,
                ],
            ],
        ];
    }

    public function getRootLabel(Model $model): string
    {
        $date = $model->contract_date ? $model->contract_date->format('Y-m-d') : '';
        $type = $model->contract_type ?? 'Engagement';
        return "{$type} Letter ({$date}) - {$model->client_name}";
    }
}

