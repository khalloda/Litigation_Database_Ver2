<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Opponent extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'opponent_name_ar',
        'opponent_name_en',
        'description',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();
        $name = $locale === 'ar' ? ($this->opponent_name_ar ?: $this->opponent_name_en) : ($this->opponent_name_en ?: $this->opponent_name_ar);
        return $name ?? '';
    }

    // Relationships
    public function cases()
    {
        return $this->belongsToMany(\App\Models\CaseModel::class, 'case_opponents', 'opponent_id', 'case_id')
            ->withPivot(['capacity_id', 'is_primary', 'display_order', 'alias_text', 'id'])
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['opponent_name_ar', 'opponent_name_en', 'description', 'notes', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('opponent')
            ->setDescriptionForEvent(fn(string $eventName) => "Opponent was {$eventName}");
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
