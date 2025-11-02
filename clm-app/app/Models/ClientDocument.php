<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ClientDocument extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles, LogsActivity;

    protected $fillable = [
        'client_id',
        'matter_id',
        'client_name',
        'document_name', // File-related field (nullable for physical docs)
        'document_type', // File-related field
        'file_path', // File-related field (nullable for physical docs)
        'file_size', // File-related field (nullable for physical docs)
        'mime_type', // File-related field (nullable for physical docs)
        'document_storage_type', // New: 'physical', 'digital', 'both'
        'mfiles_uploaded', // New: boolean for M-Files integration
        'mfiles_id', // New: M-Files document ID
        'responsible_lawyer',
        'movement_card',
        // Map UI attribute 'description' to DB column via accessors/mutators
        'description',
        'deposit_date',
        'document_date',
        'case_number',
        'pages_count',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'document_date' => 'date',
        'movement_card' => 'boolean',
        'mfiles_uploaded' => 'boolean',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'matter_id');
    }

    // Attribute mapping: description <-> document_description
    public function getDescriptionAttribute(): ?string
    {
        return $this->attributes['document_description'] ?? null;
    }

    public function setDescriptionAttribute($value): void
    {
        $this->attributes['document_description'] = $value;
    }

    // Document type constants
    const STORAGE_TYPE_PHYSICAL = 'physical';
    const STORAGE_TYPE_DIGITAL = 'digital';
    const STORAGE_TYPE_BOTH = 'both';

    // Validation methods
    public function requiresFileUpload(): bool
    {
        return in_array($this->document_storage_type, [self::STORAGE_TYPE_DIGITAL, self::STORAGE_TYPE_BOTH]);
    }

    public function isPhysicalDocument(): bool
    {
        return in_array($this->document_storage_type, [self::STORAGE_TYPE_PHYSICAL, self::STORAGE_TYPE_BOTH]);
    }

    public function isDigitalDocument(): bool
    {
        return in_array($this->document_storage_type, [self::STORAGE_TYPE_DIGITAL, self::STORAGE_TYPE_BOTH]);
    }

    public function hasMfilesIntegration(): bool
    {
        return $this->mfiles_uploaded && !empty($this->mfiles_id);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'matter_id', 'client_name', 'responsible_lawyer', 'description', 'deposit_date', 'case_number', 'document_name', 'document_type', 'file_path', 'file_size', 'mime_type', 'document_storage_type', 'mfiles_uploaded', 'mfiles_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('clientdocument')
            ->setDescriptionForEvent(fn(string $eventName) => "ClientDocument was {$eventName}");
    }
}
