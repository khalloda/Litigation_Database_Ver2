<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contact extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles, LogsActivity;

    protected $fillable = [
        'client_id', 'contact_name', 'contact_type', 'contact_value', 'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Relationships
    public function client() { return $this->belongsTo(Client::class); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'contact_name', 'contact_type', 'contact_value', 'is_primary'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('contact')
            ->setDescriptionForEvent(fn(string $eventName) => "Contact was {$eventName}");
    }
}