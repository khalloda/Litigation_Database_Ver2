<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OptionSet extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'key',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function optionValues()
    {
        return $this->hasMany(OptionValue::class, 'set_id')->orderBy('position');
    }

    public function activeOptionValues()
    {
        return $this->hasMany(OptionValue::class, 'set_id')->where('is_active', true)->orderBy('position');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    // Accessors
    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }

    // Helper methods
    public function getOptionValueByCode(string $code)
    {
        return $this->optionValues()->where('code', $code)->first();
    }

    public function getOptionValueByLabel(string $label, string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        
        if ($locale === 'ar') {
            return $this->optionValues()->where('label_ar', $label)->first();
        }
        
        return $this->optionValues()->where('label_en', $label)->first();
    }

    public function getUsageCount()
    {
        // This will be implemented based on which models use this option set
        // For now, we'll return 0 and implement specific counts in each model
        return 0;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'name_en', 'name_ar', 'description_en', 'description_ar', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('optionset')
            ->setDescriptionForEvent(fn(string $eventName) => "OptionSet was {$eventName}");
    }
}