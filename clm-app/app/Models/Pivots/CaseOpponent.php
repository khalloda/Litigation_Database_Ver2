<?php

namespace App\Models\Pivots;

use App\Models\CaseModel;
use App\Models\Opponent;
use App\Models\OptionValue;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CaseOpponent extends Pivot
{
    use SoftDeletes, LogsActivity;

    protected $table = 'case_opponents';

    protected $fillable = [
        'case_id',
        'opponent_id',
        'capacity_id',
        'is_primary',
        'display_order',
        'alias_text',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'display_order' => 'integer',
    ];

    // Relationships
    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function opponent()
    {
        return $this->belongsTo(Opponent::class);
    }

    public function capacity()
    {
        return $this->belongsTo(OptionValue::class, 'capacity_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['case_id', 'opponent_id', 'capacity_id', 'is_primary', 'display_order', 'alias_text'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('case_opponent')
            ->setDescriptionForEvent(fn(string $eventName) => "Case opponent relationship was {$eventName}");
    }
}
