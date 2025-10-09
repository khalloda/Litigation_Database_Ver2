<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Lawyer extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles, LogsActivity;

    protected $fillable = [
        'lawyer_name_ar',
        'lawyer_name_en',
        'lawyer_name_title',
        'lawyer_email',
        'attendance_track',
    ];

    protected $casts = [
        'attendance_track' => 'boolean',
    ];

    // Relationships
    public function cases()
    {
        return $this->hasMany(CaseModel::class);
    }

    public function adminTasks()
    {
        return $this->hasMany(AdminTask::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['lawyer_name_ar', 'lawyer_name_en', 'lawyer_name_title', 'lawyer_email', 'attendance_track'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('lawyer')
            ->setDescriptionForEvent(fn(string $eventName) => "Lawyer was {$eventName}");
    }
}