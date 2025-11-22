<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'table_name',
        'header_hash',
        'is_active',
        'settings_json',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings_json' => 'array',
    ];

    public function choices()
    {
        return $this->hasMany(ImportChoice::class, 'profile_id');
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

    public function scopeWithHeaderHashOrNull($query, ?string $hash)
    {
        return $hash
            ? $query->where(function ($q) use ($hash) {
                $q->where('header_hash', $hash)
                    ->orWhereNull('header_hash');
            })
            : $query->whereNull('header_hash');
    }
}

