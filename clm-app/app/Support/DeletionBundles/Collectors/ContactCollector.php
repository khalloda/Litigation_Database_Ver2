<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;

class ContactCollector extends BaseCollector
{
    public function collect(Model $model): array
    {
        return [
            'contact' => [
                'attributes' => $this->getAttributes($model),
                'references' => [
                    'client_id' => $model->client_id,
                ],
            ],
        ];
    }

    public function getRootLabel(Model $model): string
    {
        return $model->full_name ?? $model->contact_name ?? "Contact #{$model->id}";
    }
}

