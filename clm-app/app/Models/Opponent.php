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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['opponent_name_ar', 'opponent_name_en', 'description', 'notes', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('opponent')
            ->setDescriptionForEvent(fn(string $eventName) => "Opponent was {$eventName}");
    }
}
