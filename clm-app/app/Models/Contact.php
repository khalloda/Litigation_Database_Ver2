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
        'client_id', 'contact_name', 'full_name', 'job_title', 'address', 'city', 'state', 'country', 'zip_code', 
        'business_phone', 'home_phone', 'mobile_phone', 'fax_number', 'email', 'web_page', 'attachments'
    ];

    protected $casts = [
        // No special casts needed for contact model
    ];

    // Relationships
    public function client() { return $this->belongsTo(Client::class); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'contact_name', 'full_name', 'job_title', 'email', 'business_phone'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('contact')
            ->setDescriptionForEvent(fn(string $eventName) => "Contact was {$eventName}");
    }
}