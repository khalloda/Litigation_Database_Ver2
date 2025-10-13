<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Court extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'court_name_ar',
        'court_name_en',
        'court_circuit',
        'court_circuit_secretary',
        'court_floor',
        'court_hall',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function courtCircuit()
    {
        return $this->belongsTo(OptionValue::class, 'court_circuit');
    }

    public function courtCircuitSecretary()
    {
        return $this->belongsTo(OptionValue::class, 'court_circuit_secretary');
    }

    public function courtFloor()
    {
        return $this->belongsTo(OptionValue::class, 'court_floor');
    }

    public function courtHall()
    {
        return $this->belongsTo(OptionValue::class, 'court_hall');
    }

    public function cases()
    {
        return $this->hasMany(CaseModel::class, 'court_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessor for locale-aware court name
    public function getCourtNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->court_name_ar : $this->court_name_en;
    }

    // Activity log configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['court_name_ar', 'court_name_en', 'court_circuit', 'court_circuit_secretary', 'court_floor', 'court_hall', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('court')
            ->setDescriptionForEvent(fn(string $eventName) => "Court was {$eventName}");
    }
}
