<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CaseModel extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles, LogsActivity;

    protected $table = 'cases';

    protected $fillable = [
        'client_id',
        'contract_id',
        'matter_name_ar',
        'matter_name_en',
        'matter_description',
        'matter_status',
        'matter_category',
        'matter_degree',
        'court_id',
        'matter_court_text',
        'matter_circuit',
        'matter_destination',
        'matter_importance',
        'matter_evaluation',
        'matter_start_date',
        'matter_end_date',
        'matter_asked_amount',
        'matter_judged_amount',
        'matter_shelf',
        'matter_partner',
        'lawyer_a',
        'lawyer_b',
        'circuit_secretary',
        'court_floor',
        'court_hall',
        'fee_letter',
        'team_id',
        'legal_opinion',
        'financial_provision',
        'current_status',
        'notes_1',
        'notes_2',
        'client_and_capacity',
        'opponent_and_capacity',
        'client_branch',
        'client_type',
        'matter_select',
    ];

    protected $casts = [
        'matter_start_date' => 'date',
        'matter_end_date' => 'date',
        'matter_asked_amount' => 'decimal:2',
        'matter_judged_amount' => 'decimal:2',
        'fee_letter' => 'decimal:2',
        'matter_select' => 'boolean',
        'court_floor' => 'integer',
        'court_hall' => 'integer',
        'team_id' => 'integer',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contract()
    {
        return $this->belongsTo(EngagementLetter::class, 'contract_id');
    }

    public function hearings()
    {
        return $this->hasMany(Hearing::class, 'matter_id');
    }

    public function adminTasks()
    {
        return $this->hasMany(AdminTask::class, 'matter_id');
    }

    public function documents()
    {
        return $this->hasMany(ClientDocument::class, 'matter_id');
    }

    public function court()
    {
        return $this->belongsTo(Court::class, 'court_id');
    }

    public function matterCircuit()
    {
        return $this->belongsTo(OptionValue::class, 'matter_circuit');
    }

    public function circuitSecretaryRef()
    {
        return $this->belongsTo(OptionValue::class, 'circuit_secretary');
    }

    public function courtFloorRef()
    {
        return $this->belongsTo(OptionValue::class, 'court_floor');
    }

    public function courtHallRef()
    {
        return $this->belongsTo(OptionValue::class, 'court_hall');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'contract_id', 'matter_name_ar', 'matter_name_en', 'matter_status', 'court_id', 'matter_start_date', 'matter_end_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('case')
            ->setDescriptionForEvent(fn(string $eventName) => "Case was {$eventName}");
    }
}
