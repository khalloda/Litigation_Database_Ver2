<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportChoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'table_name',
        'column',
        'raw_value',
        'normalized_value',
        'action',
        'entity_model',
        'entity_id',
        'metadata_json',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata_json' => 'array',
    ];

    public function profile()
    {
        return $this->belongsTo(ImportProfile::class, 'profile_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    public function scopeForColumn($query, string $column)
    {
        return $query->where('column', $column);
    }

    public function scopeForValue($query, string $normalized)
    {
        return $query->where('normalized_value', $normalized);
    }
}

