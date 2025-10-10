<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeletionBundle extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'root_type',
        'root_id',
        'root_label',
        'snapshot_json',
        'files_json',
        'cascade_count',
        'deleted_by',
        'reason',
        'status',
        'ttl_at',
        'restored_at',
        'restore_notes',
    ];

    protected $casts = [
        'snapshot_json' => 'array',
        'files_json' => 'array',
        'cascade_count' => 'integer',
        'ttl_at' => 'datetime',
        'restored_at' => 'datetime',
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
     * Get the user who deleted this bundle.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get all items in this bundle.
     */
    public function items()
    {
        return $this->hasMany(DeletionBundleItem::class, 'bundle_id');
    }

    /**
     * Check if bundle is trashed.
     */
    public function isTrashed(): bool
    {
        return $this->status === 'trashed';
    }

    /**
     * Check if bundle is restored.
     */
    public function isRestored(): bool
    {
        return $this->status === 'restored';
    }

    /**
     * Check if bundle is purged.
     */
    public function isPurged(): bool
    {
        return $this->status === 'purged';
    }

    /**
     * Check if bundle is expired (past TTL).
     */
    public function isExpired(): bool
    {
        return $this->ttl_at && $this->ttl_at->isPast();
    }

    /**
     * Scope to only trashed bundles.
     */
    public function scopeTrashed($query)
    {
        return $query->where('status', 'trashed');
    }

    /**
     * Scope to only restored bundles.
     */
    public function scopeRestored($query)
    {
        return $query->where('status', 'restored');
    }

    /**
     * Scope to only purged bundles.
     */
    public function scopePurged($query)
    {
        return $query->where('status', 'purged');
    }

    /**
     * Scope to expired bundles.
     */
    public function scopeExpired($query)
    {
        return $query->where('ttl_at', '<', now());
    }

    /**
     * Scope by root type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('root_type', $type);
    }
}
