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
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles, LogsActivity;

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
    }

    public function lawyer()
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['task_id', 'lawyer_id', 'performer', 'next_date', 'result', 'procedure_date', 'report'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('admin_subtask')
            ->setDescriptionForEvent(fn($eventName) => "Admin subtask was {$eventName}");
    }
}