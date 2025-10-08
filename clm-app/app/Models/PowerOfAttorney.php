<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    }
}
