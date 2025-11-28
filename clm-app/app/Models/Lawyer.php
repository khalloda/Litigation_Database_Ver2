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
        'title_id',
        'lawyer_email',
        'attendance_track',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'attendance_track' => 'boolean',
    ];

    // Relationships
    // Cases where this lawyer is lawyer A
    public function casesAsLawyerA()
    {
        return $this->hasMany(CaseModel::class, 'lawyer_a', 'id');
    }

    // Cases where this lawyer is lawyer B
    public function casesAsLawyerB()
    {
        return $this->hasMany(CaseModel::class, 'lawyer_b', 'id');
    }

    // Get all cases (lawyer A or lawyer B) - not a relationship, returns collection
    public function getAllCases()
    {
        return CaseModel::where('lawyer_a', $this->id)
            ->orWhere('lawyer_b', $this->id)
            ->get();
    }

    public function adminTasks()
    {
        return $this->hasMany(AdminTask::class);
    }

    public function title()
    {
        return $this->belongsTo(OptionValue::class, 'title_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['lawyer_name_ar', 'lawyer_name_en', 'lawyer_name_title', 'title_id', 'lawyer_email', 'attendance_track'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('lawyer')
            ->setDescriptionForEvent(fn(string $eventName) => "Lawyer was {$eventName}");
    }
}
