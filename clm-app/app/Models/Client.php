<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Client extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles, LogsActivity;

    protected $fillable = [
        'client_name_ar',
        'client_name_en',
        'client_print_name',
        'status',
        'cash_or_probono',
        'cash_or_probono_id', // New FK
        'status_id', // New FK
        'power_of_attorney_location_id', // New FK
        'documents_location_id', // New FK
        'client_start',
        'client_end',
        'contact_lawyer',
        'contact_lawyer_id', // New FK
        'logo',
        'power_of_attorney_location',
        'documents_location',
    ];

    protected $casts = [
        'client_start' => 'date',
        'client_end' => 'date',
    ];

    // Relationships
    public function cases()
    {
        return $this->hasMany(CaseModel::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function engagementLetters()
    {
        return $this->hasMany(EngagementLetter::class);
    }

    public function powerOfAttorneys()
    {
        return $this->hasMany(PowerOfAttorney::class);
    }

    // Option relationships
    public function cashOrProbono()
    {
        return $this->belongsTo(OptionValue::class, 'cash_or_probono_id');
    }

    public function statusRef()
    {
        return $this->belongsTo(OptionValue::class, 'status_id');
    }

    public function powerOfAttorneyLocation()
    {
        return $this->belongsTo(OptionValue::class, 'power_of_attorney_location_id');
    }

    public function documentsLocation()
    {
        return $this->belongsTo(OptionValue::class, 'documents_location_id');
    }

    public function contactLawyer()
    {
        return $this->belongsTo(Lawyer::class, 'contact_lawyer_id');
    }

    public function documents()
    {
        return $this->hasMany(ClientDocument::class);
    }

    // Accessors for localized labels
    public function getCashOrProbonoLabelAttribute()
    {
        return $this->cashOrProbono?->label ?? $this->cash_or_probono;
    }

    public function getStatusLabelAttribute()
    {
        return $this->statusRef?->label ?? $this->status;
    }

    public function getPowerOfAttorneyLocationLabelAttribute()
    {
        return $this->powerOfAttorneyLocation?->label ?? $this->power_of_attorney_location;
    }

    public function getDocumentsLocationLabelAttribute()
    {
        return $this->documentsLocation?->label ?? $this->documents_location;
    }

    public function getContactLawyerNameAttribute()
    {
        if ($this->contactLawyer) {
            return app()->getLocale() === 'ar' 
                ? $this->contactLawyer->lawyer_name_ar 
                : $this->contactLawyer->lawyer_name_en;
        }
        return $this->contact_lawyer;
    }

    // Scopes for filtering by option values
    public function scopeCashOrProbono($query, array $codes)
    {
        return $query->whereHas('cashOrProbono', function ($q) use ($codes) {
            $q->whereIn('code', $codes);
        });
    }

    public function scopeStatus($query, array $codes)
    {
        return $query->whereHas('statusRef', function ($q) use ($codes) {
            $q->whereIn('code', $codes);
        });
    }

    public function scopePowerOfAttorneyLocation($query, array $codes)
    {
        return $query->whereHas('powerOfAttorneyLocation', function ($q) use ($codes) {
            $q->whereIn('code', $codes);
        });
    }

    public function scopeDocumentsLocation($query, array $codes)
    {
        return $query->whereHas('documentsLocation', function ($q) use ($codes) {
            $q->whereIn('code', $codes);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_name_ar', 'client_name_en', 'client_print_name', 'status', 'cash_or_probono', 'cash_or_probono_id', 'status_id', 'power_of_attorney_location_id', 'documents_location_id', 'client_start', 'client_end', 'contact_lawyer', 'contact_lawyer_id', 'logo', 'power_of_attorney_location', 'documents_location'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('client')
            ->setDescriptionForEvent(fn(string $eventName) => "Client was {$eventName}");
    }
}
