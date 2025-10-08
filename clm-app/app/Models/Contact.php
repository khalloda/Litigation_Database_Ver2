<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles;

    protected $fillable = [
        'client_id',
        'contact_name',
        'full_name',
        'job_title',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'business_phone',
        'home_phone',
        'mobile_phone',
        'fax_number',
        'email',
        'web_page',
        'attachments',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
