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
        'engagement_letter_no',
        'client_in_case_name',
        'opponent_in_case_name',
        'matter_name_ar',
        'matter_name_en',
        'matter_description',
        'matter_status',
        'matter_status_id',
        'matter_category',
        'matter_category_id',
        'matter_degree',
        'matter_degree_id',
        'court_id',
        'matter_court_text',
        'matter_circuit_legacy',
        'circuit_name_id',
        'circuit_serial_id',
        'circuit_shift_id',
        'matter_destination', // legacy text
        'matter_destination_id',
        'matter_importance',
        'matter_importance_id',
        'matter_evaluation',
        'matter_start_date',
        'matter_end_date',
        'matter_asked_amount',
        'matter_judged_amount',
        'matter_shelf',
        'matter_partner', // legacy text
        'matter_partner_id',
        'lawyer_a',
        'lawyer_b',
        'circuit_secretary',
        'court_floor',
        'court_hall',
        'fee_letter',
        'allocated_budget',
        'team_id',
        'legal_opinion',
        'financial_provision',
        'current_status',
        'notes_1',
        'notes_2',
        'client_and_capacity', // legacy text
        'opponent_and_capacity', // legacy text
        'client_branch',
        'matter_branch_id',
        'client_type', // legacy text
        'client_type_id',
        'client_capacity_id',
        'client_capacity_note',
        'opponent_id',
        'opponent_capacity_id',
        'opponent_capacity_note',
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

    // New option set relationships
    public function matterCategory()
    {
        return $this->belongsTo(OptionValue::class, 'matter_category_id');
    }

    public function matterDegree()
    {
        return $this->belongsTo(OptionValue::class, 'matter_degree_id');
    }

    public function matterStatus()
    {
        return $this->belongsTo(OptionValue::class, 'matter_status_id');
    }

    public function matterImportance()
    {
        return $this->belongsTo(OptionValue::class, 'matter_importance_id');
    }

    public function matterBranch()
    {
        return $this->belongsTo(OptionValue::class, 'matter_branch_id');
    }

    public function clientCapacity()
    {
        return $this->belongsTo(OptionValue::class, 'client_capacity_id');
    }

    public function clientType()
    {
        return $this->belongsTo(OptionValue::class, 'client_type_id');
    }

    public function opponent()
    {
        return $this->belongsTo(Opponent::class, 'opponent_id');
    }

    public function opponentCapacity()
    {
        return $this->belongsTo(OptionValue::class, 'opponent_capacity_id');
    }

    public function matterDestinationRef()
    {
        return $this->belongsTo(Court::class, 'matter_destination_id');
    }

    public function matterPartnerRef()
    {
        return $this->belongsTo(Lawyer::class, 'matter_partner_id');
    }

    public function circuitName()
    {
        return $this->belongsTo(OptionValue::class, 'circuit_name_id');
    }

    public function circuitSerial()
    {
        return $this->belongsTo(OptionValue::class, 'circuit_serial_id');
    }

    public function circuitShift()
    {
        return $this->belongsTo(OptionValue::class, 'circuit_shift_id');
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
            ->logOnly([
                'client_id','client_in_case_name','client_capacity_id','client_capacity_note','client_type_id',
                'opponent_id','opponent_in_case_name','opponent_capacity_id','opponent_capacity_note',
                'contract_id','engagement_letter_no',
                'matter_name_ar','matter_name_en','matter_description',
                'matter_status_id','matter_category_id','matter_degree_id','matter_importance_id','matter_branch_id',
                'court_id','matter_destination_id',
                'circuit_name_id','circuit_serial_id','circuit_shift_id',
                'matter_start_date','matter_end_date','allocated_budget','fee_letter','matter_shelf',
                'matter_partner_id','lawyer_a','lawyer_b',
                'matter_evaluation','legal_opinion','current_status','notes_1','notes_2'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('case')
            ->setDescriptionForEvent(fn(string $eventName) => "Case was {$eventName}");
    }
}
