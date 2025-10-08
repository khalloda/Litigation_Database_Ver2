<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_name_ar',
        'client_name_en',
        'client_print_name',
        'status',
        'cash_or_probono',
        'client_start',
        'client_end',
        'contact_lawyer',
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

    public function documents()
    {
        return $this->hasMany(ClientDocument::class);
    }
}
