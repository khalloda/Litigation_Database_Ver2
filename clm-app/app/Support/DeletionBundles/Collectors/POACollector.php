<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;

class POACollector extends BaseCollector
{
    public function collect(Model $model): array
    {
        return [
            'power_of_attorney' => [
                'attributes' => $this->getAttributes($model),
                'references' => [
                    'client_id' => $model->client_id,
                ],
            ],
        ];
    }

    public function getRootLabel(Model $model): string
    {
        $poaNumber = $model->poa_number ?? 'N/A';
        $year = $model->year ?? '';
        return "POA {$poaNumber}/{$year}";
    }
}

