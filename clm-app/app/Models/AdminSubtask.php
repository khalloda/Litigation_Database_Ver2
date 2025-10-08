<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminSubtask extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles;

    protected $fillable = [
        'task_id',
        'lawyer_id',
        'performer',
        'next_date',
        'result',
        'procedure_date',
        'report',
    ];

    protected $casts = [
        'next_date' => 'date',
        'procedure_date' => 'date',
        'report' => 'boolean',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(AdminTask::class, 'task_id');
    }

    public function lawyer()
    {
        return $this->belongsTo(Lawyer::class);
    }
}
