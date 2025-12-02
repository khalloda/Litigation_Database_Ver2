<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PowerOfAttorneyMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'power_of_attorney_id',
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

    public function powerOfAttorney()
    {
        return $this->belongsTo(PowerOfAttorney::class);
    }

    public function lawyer()
    {
        return $this->belongsTo(Lawyer::class);
    }
}


