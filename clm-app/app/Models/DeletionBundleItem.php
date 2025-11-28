<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeletionBundleItem extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'bundle_id',
        'model',
        'model_id',
        'payload_json',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'model_id' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the bundle this item belongs to.
     */
    public function bundle()
    {
        return $this->belongsTo(DeletionBundle::class, 'bundle_id');
    }

    /**
     * Get the model class name.
     */
    public function getModelClass(): string
    {
        return "App\\Models\\{$this->model}";
    }

    /**
     * Check if this item has a model ID (existing record).
     */
    public function hasModelId(): bool
    {
        return !is_null($this->model_id);
    }

    /**
     * Scope by model type.
     */
    public function scopeOfModel($query, string $model)
    {
        return $query->where('model', $model);
    }
}
