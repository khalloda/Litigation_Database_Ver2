<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Hearing extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles, LogsActivity;

    protected $fillable = [
        'matter_id',
        'date',
        'time',
        'court',
        'judge',
        'status',
        'notes',
        'next_hearing',
        'report',
        'notify_client',
    ];

    protected $casts = [
        'date' => 'date',
        'next_hearing' => 'date',
        'report' => 'boolean',
        'notify_client' => 'boolean',
    ];

    // Relationships
    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'matter_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['date', 'time', 'court', 'judge', 'status', 'notes', 'matter_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('hearing')
            ->setDescriptionForEvent(fn(string $eventName) => "Hearing was {$eventName}");
    }
}