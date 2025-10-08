<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hearing extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles;

    protected $fillable = [
        'matter_id',
        'lawyer_id',
        'date',
        'procedure',
        'court',
        'circuit',
        'destination',
        'decision',
        'short_decision',
        'last_decision',
        'next_hearing',
        'report',
        'notify_client',
        'attendee',
        'attendee_1',
        'attendee_2',
        'attendee_3',
        'attendee_4',
        'next_attendee',
        'evaluation',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'next_hearing' => 'date',
        'report' => 'boolean',
        'notify_client' => 'boolean',
    ];

    // Relationships
    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'matter_id');
    }

    public function lawyer()
    {
        return $this->belongsTo(Lawyer::class);
    }
}
