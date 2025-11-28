<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDocumentStaging extends Model
{
    use HasFactory;

    protected $table = 'client_documents_staging';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'legacy_document_id',
        'legacy_client_id',
        'client_name',
        'legacy_matter_name',
        'document_description',
        'document_date_raw',
        'document_date',
        'pages_count_raw',
        'pages_count',
        'deposit_date_raw',
        'deposit_date',
        'department',
        'admin_staff',
        'lawyer',
        'responsible_lawyer',
        'notes',
        'movement_card_raw',
        'movement_card',
        'raw_payload',
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'document_date' => 'date',
        'movement_card' => 'boolean',
        'raw_payload' => 'array',
    ];
}

