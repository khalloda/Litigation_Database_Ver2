<?php

namespace App\Support\DeletionBundles;

use App\Services\DeletionBundleService;
use Illuminate\Support\Facades\Log;

trait InteractsWithDeletionBundles
{
    /**
     * Boot the trait.
     */
    public static function bootInteractsWithDeletionBundles(): void
    {
        // Hook into the deleting event
        static::deleting(function ($model) {
            // Only create bundle for non-force deletes
            if (!$model->isForceDeleting()) {
                try {
                    $service = app(DeletionBundleService::class);
                    $bundleId = $service->createBundle(
                        $model,
                        static::getDeletionReason()
                    );

                    // Store bundle ID in model for reference
                    $model->deletion_bundle_id = $bundleId;

                    Log::info("Deletion bundle created for model", [
                        'model' => get_class($model),
                        'id' => $model->id,
                        'bundle_id' => $bundleId,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to create deletion bundle", [
                        'model' => get_class($model),
                        'id' => $model->id,
                        'error' => $e->getMessage(),
                    ]);

                    // Optionally, you can prevent deletion if bundle creation fails
                    // throw $e;
                }
            }
        });
    }

    /**
     * Get deletion reason (can be overridden in models).
     */
    protected static function getDeletionReason(): ?string
    {
        return request()->input('deletion_reason') ?? null;
    }

    /**
     * Check if model is being force deleted.
     */
    public function isForceDeleting(): bool
    {
        return $this->forceDeleting ?? false;
    }

    /**
     * Disable bundle creation for this deletion.
     */
    public function withoutBundle(): self
    {
        $this->skipBundleCreation = true;
        return $this;
    }

    /**
     * Check if bundle creation should be skipped.
     */
    public function shouldSkipBundleCreation(): bool
    {
        return $this->skipBundleCreation ?? false;
    }
}
