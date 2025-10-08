<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminTask extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles;

    protected $fillable = [
        'matter_id',
        'lawyer_id',
        'last_follow_up',
        'last_date',
        'authority',
        'status',
        'circuit',
        'required_work',
        'performer',
        'previous_decision',
        'court',
        'result',
        'creation_date',
        'execution_date',
        'alert',
    ];

    protected $casts = [
        'last_date' => 'date',
        'creation_date' => 'datetime',
        'execution_date' => 'datetime',
        'alert' => 'boolean',
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

    public function subtasks()
    {
        return $this->hasMany(AdminSubtask::class, 'task_id');
    }
}
