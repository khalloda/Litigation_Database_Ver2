<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;

abstract class BaseCollector implements CollectorInterface
{
    /**
     * Get file descriptors if applicable.
     * Override in child classes that handle files.
     */
    public function getFileDescriptors(array $snapshot): array
    {
        return [];
    }

    /**
     * Helper to get model attributes as array.
     */
    protected function getAttributes(Model $model): array
    {
        return $model->getAttributes();
    }

    /**
     * Helper to collect related models.
     */
    protected function collectRelated($relation, string $key = 'attributes'): array
    {
        if (!$relation) {
            return [];
        }

        $items = $relation->get();
        
        return $items->map(function ($item) use ($key) {
            return [$key => $item->getAttributes()];
        })->toArray();
    }

    /**
     * Helper to create file descriptor.
     */
    protected function createFileDescriptor(?string $disk, ?string $path, ?int $size = null, ?string $mime = null): array
    {
        if (!$disk || !$path) {
            return [];
        }

        return [
            'disk' => $disk,
            'path' => $path,
            'size' => $size,
            'mime' => $mime,
        ];
    }
}

