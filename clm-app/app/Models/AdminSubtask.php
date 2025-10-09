<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AdminSubtask extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles;

    protected $fillable = [
        'task_id',
        'lawyer_id',
        'performer',
        'next_date',
        'result',
        'procedure_date',
        'report',
    ];

    protected $casts = [
        'next_date' => 'date',
        'procedure_date' => 'date',
        'report' => 'boolean',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(AdminTask::class, 'task_id');
    
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['task_id', 'subtask_name', 'subtask_description', 'status', 'due_date', 'completed_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('adminsubtask')
            ->setDescriptionForEvent(fn(string $eventName) => "AdminSubtask was {$eventName}");
    }")
            ->setDescriptionForEvent('updated', fn(string $eventName) => "AdminSubtask was {$eventName}")
            ->setDescriptionForEvent('deleted', fn(string $eventName) => "AdminSubtask was {$eventName}");
    }
}

    public function lawyer()
    {
        return $this->belongsTo(Lawyer::class);
    
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['task_id', 'subtask_name', 'subtask_description', 'status', 'due_date', 'completed_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('adminsubtask')
            ->setDescriptionForEvent(fn(string $eventName) => "AdminSubtask was {$eventName}");
    }")
            ->setDescriptionForEvent('updated', fn(string $eventName) => "AdminSubtask was {$eventName}")
            ->setDescriptionForEvent('deleted', fn(string $eventName) => "AdminSubtask was {$eventName}");
    }
}

    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['task_id', 'subtask_name', 'subtask_description', 'status', 'due_date', 'completed_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('adminsubtask')
            ->setDescriptionForEvent(fn(string $eventName) => "AdminSubtask was {$eventName}");
    }")
            ->setDescriptionForEvent('updated', fn(string $eventName) => "AdminSubtask was {$eventName}")
            ->setDescriptionForEvent('deleted', fn(string $eventName) => "AdminSubtask was {$eventName}");
    }
}
