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
        'id',
        'legacy_document_id',
        'client_id',
        'matter_id',
        'legacy_matter_name',
        'client_name',
        'document_name', // File-related field (nullable for physical docs)
        'document_type', // File-related field
        'file_path', // File-related field (nullable for physical docs)
        'file_size', // File-related field (nullable for physical docs)
        'mime_type', // File-related field (nullable for physical docs)
        'document_storage_type', // New: 'physical', 'digital', 'both'
        'mfiles_uploaded', // New: boolean for M-Files integration
        'mfiles_id', // New: M-Files document ID
        'department',
        'admin_staff',
        'lawyer',
        'responsible_lawyer',
        'movement_card',
        'document_location',
        // Map UI attribute 'description' to DB column via accessors/mutators
        'description',
        'document_description',
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

    protected static function booted(): void
    {
        static::saving(function (ClientDocument $document) {
            if (!$document->client_id) {
                $document->document_location = null;
                return;
            }

            $client = $document->relationLoaded('client')
                ? $document->client
                : Client::with('documentsLocation')
                ->select('id', 'documents_location_id')
                ->find($document->client_id);

            $document->document_location = $client?->documentsLocation?->label;
        });
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'matter_id');
    }

    public function movements()
    {
        return $this->hasMany(DocumentMovement::class, 'document_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
            ->logOnly([
                'legacy_document_id',
                'client_id',
                'matter_id',
                'client_name',
                'legacy_matter_name',
                'department',
                'admin_staff',
                'lawyer',
                'document_location',
                'responsible_lawyer',
                'description',
                'deposit_date',
                'case_number',
                'document_name',
                'document_type',
                'file_path',
                'file_size',
                'mime_type',
                'document_storage_type',
                'mfiles_uploaded',
                'mfiles_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('clientdocument')
            ->setDescriptionForEvent(fn(string $eventName) => "ClientDocument was {$eventName}");
    }
}
