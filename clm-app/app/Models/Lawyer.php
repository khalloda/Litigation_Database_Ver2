<?php

namespace App\Models;

use App\Support\DeletionBundles\InteractsWithDeletionBundles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lawyer extends Model
{
    use HasFactory, SoftDeletes, InteractsWithDeletionBundles;

    protected $fillable = [
        'lawyer_name_ar',
        'lawyer_name_en',
        'lawyer_name_title',
        'lawyer_email',
        'attendance_track',
    ];

    protected $casts = [
        'attendance_track' => 'boolean',
    ];

    // Relationships
    public function hearings()
    {
        return $this->hasMany(Hearing::class);
    }

    public function adminTasks()
    {
        return $this->hasMany(AdminTask::class);
    }

    public function adminSubtasks()
    {
        return $this->hasMany(AdminSubtask::class);
    }
}
