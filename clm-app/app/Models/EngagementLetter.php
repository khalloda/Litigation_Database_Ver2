<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EngagementLetter extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles;

    protected $fillable = [
        'client_id',
        'client_name',
        'contract_date',
        'contract_details',
        'contract_structure',
        'contract_type',
        'matters',
        'status',
        'mfiles_id',
    ];

    protected $casts = [
        'contract_date' => 'datetime',
        'mfiles_id' => 'integer',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'contract_number', 'issue_date', 'expiry_date', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('engagementletter')
            ->setDescriptionForEvent(fn(string $eventName) => "EngagementLetter was {$eventName}");
    }")
            ->setDescriptionForEvent('updated', fn(string $eventName) => "EngagementLetter was {$eventName}")
            ->setDescriptionForEvent('deleted', fn(string $eventName) => "EngagementLetter was {$eventName}");
    }
}

    public function cases()
    {
        return $this->hasMany(CaseModel::class, 'contract_id');
    
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'contract_number', 'issue_date', 'expiry_date', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('engagementletter')
            ->setDescriptionForEvent(fn(string $eventName) => "EngagementLetter was {$eventName}");
    }")
            ->setDescriptionForEvent('updated', fn(string $eventName) => "EngagementLetter was {$eventName}")
            ->setDescriptionForEvent('deleted', fn(string $eventName) => "EngagementLetter was {$eventName}");
    }
}

    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'contract_number', 'issue_date', 'expiry_date', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('engagementletter')
            ->setDescriptionForEvent(fn(string $eventName) => "EngagementLetter was {$eventName}");
    }")
            ->setDescriptionForEvent('updated', fn(string $eventName) => "EngagementLetter was {$eventName}")
            ->setDescriptionForEvent('deleted', fn(string $eventName) => "EngagementLetter was {$eventName}");
    }
}
