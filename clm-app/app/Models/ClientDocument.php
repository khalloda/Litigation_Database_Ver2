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
        'document_name', // New file-related field
        'document_type', // New file-related field
        'file_path', // New file-related field
        'file_size', // New file-related field
        'mime_type', // New file-related field
        'responsible_lawyer',
        'movement_card',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'matter_id', 'client_name', 'responsible_lawyer', 'document_description', 'deposit_date', 'case_number'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('clientdocument')
            ->setDescriptionForEvent(fn(string $eventName) => "ClientDocument was {$eventName}");
    }
}