<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;

interface CollectorInterface
{
    /**
     * Collect snapshot data for the given model.
     *
     * @param Model $model The model being deleted
     * @return array Snapshot data structure
     */
    public function collect(Model $model): array;

    /**
     * Get the root label for display.
     *
     * @param Model $model
     * @return string
     */
    public function getRootLabel(Model $model): string;

    /**
     * Get file descriptors if applicable.
     *
     * @param array $snapshot
     * @return array
     */
    public function getFileDescriptors(array $snapshot): array;
}

