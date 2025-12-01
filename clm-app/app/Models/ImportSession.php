<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ImportSession extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'session_id',
        'table_name',
        'original_filename',
        'stored_filename',
        'status',
        'file_type',
        'file_size',
        'file_hash',
        'total_rows',
        'header_row',
        'column_mapping',
        'transforms',
        'preflight_errors',
        'preflight_error_count',
        'preflight_warning_count',
        'imported_count',
        'failed_count',
        'skipped_count',
        'import_errors',
        'backup_file',
        'backup_size',
        'backup_created_at',
        'started_at',
        'completed_at',
        'duration_seconds',
        'user_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'total_rows' => 'integer',
        'header_row' => 'integer',
        'column_mapping' => 'array',
        'transforms' => 'array',
        'preflight_errors' => 'array',
        'preflight_error_count' => 'integer',
        'preflight_warning_count' => 'integer',
        'imported_count' => 'integer',
        'failed_count' => 'integer',
        'skipped_count' => 'integer',
        'import_errors' => 'array',
        'backup_size' => 'integer',
        'backup_created_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    // Status constants
    const STATUS_UPLOADED = 'uploaded';
    const STATUS_MAPPED = 'mapped';
    const STATUS_VALIDATED = 'validated';
    const STATUS_IMPORTING = 'importing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user who initiated this import.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if import is in progress.
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, [
            self::STATUS_UPLOADED,
            self::STATUS_MAPPED,
            self::STATUS_VALIDATED,
            self::STATUS_IMPORTING,
        ]);
    }

    /**
     * Check if import is complete.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if import has failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get success rate percentage.
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->imported_count / $this->total_rows) * 100, 2);
    }

    /**
     * Get error rate percentage.
     */
    public function getErrorRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round((($this->failed_count + $this->skipped_count) / $this->total_rows) * 100, 2);
    }

    /**
     * Get human-readable file size.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes > 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'session_id',
                'table_name',
                'status',
                'imported_count',
                'failed_count',
                'skipped_count'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('import_session')
            ->setDescriptionForEvent(fn(string $eventName) => "Import session was {$eventName}");
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by table name.
     */
    public function scopeForTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Scope to get recent sessions.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

