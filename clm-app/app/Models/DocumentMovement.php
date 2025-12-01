<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentMovement extends Model
{
    use HasFactory;

    protected $table = 'document_movements';

    protected $fillable = [
        'document_id',
        'date',
        'from_location',
        'to_location',
        'status',
        'lawyer_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function document()
    {
        return $this->belongsTo(ClientDocument::class, 'document_id');
    }

    public function lawyer()
    {
        return $this->belongsTo(Lawyer::class, 'lawyer_id');
    }
}


