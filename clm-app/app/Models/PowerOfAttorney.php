<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PowerOfAttorney extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles;

    protected $fillable = [
        'client_id',
        'client_print_name',
        'principal_name',
        'year',
        'capacity',
        'authorized_lawyers',
        'issue_date',
        'inventory',
        'issuing_authority',
        'letter',
        'poa_number',
        'principal_capacity',
        'copies_count',
        'serial',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'inventory' => 'boolean',
        'year' => 'integer',
        'poa_number' => 'integer',
        'copies_count' => 'integer',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'poa_type', 'poa_number', 'issue_date', 'expiry_date', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('powerofattorney')
            ->setDescriptionForEvent(fn(string $eventName) => "PowerOfAttorney was {$eventName}");
    }")
            ->setDescriptionForEvent('updated', fn(string $eventName) => "PowerOfAttorney was {$eventName}")
            ->setDescriptionForEvent('deleted', fn(string $eventName) => "PowerOfAttorney was {$eventName}");
    }
}

    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'poa_type', 'poa_number', 'issue_date', 'expiry_date', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('powerofattorney')
            ->setDescriptionForEvent(fn(string $eventName) => "PowerOfAttorney was {$eventName}");
    }")
            ->setDescriptionForEvent('updated', fn(string $eventName) => "PowerOfAttorney was {$eventName}")
            ->setDescriptionForEvent('deleted', fn(string $eventName) => "PowerOfAttorney was {$eventName}");
    }
}
