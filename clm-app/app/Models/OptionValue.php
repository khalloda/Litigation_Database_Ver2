<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OptionValue extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'set_id',
        'code',
        'label_en',
        'label_ar',
        'position',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function optionSet()
    {
        return $this->belongsTo(OptionSet::class, 'set_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('label_en');
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    // Accessors
    public function getLabelAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->label_ar : $this->label_en;
    }

    // Helper methods for import mapping
    public static function findByLabelOrCode(string $value, string $setKey, string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        
        // First try to find by set key
        $optionSet = OptionSet::byKey($setKey)->first();
        if (!$optionSet) {
            return null;
        }

        // Try exact label match first
        $labelField = $locale === 'ar' ? 'label_ar' : 'label_en';
        $optionValue = $optionSet->optionValues()->where($labelField, $value)->first();
        
        if ($optionValue) {
            return $optionValue;
        }

        // Try case-insensitive label match
        $optionValue = $optionSet->optionValues()
            ->whereRaw("LOWER({$labelField}) = ?", [strtolower(trim($value))])
            ->first();
            
        if ($optionValue) {
            return $optionValue;
        }

        // Try code match
        $optionValue = $optionSet->optionValues()->byCode($value)->first();
        
        if ($optionValue) {
            return $optionValue;
        }

        // Try normalized synonyms
        $normalizedValue = self::normalizeValue($value, $setKey);
        if ($normalizedValue && $normalizedValue !== $value) {
            $optionValue = $optionSet->optionValues()->byCode($normalizedValue)->first();
            if ($optionValue) {
                return $optionValue;
            }
        }

        return null;
    }

    /**
     * Normalize common synonyms for import compatibility
     */
    public static function normalizeValue(string $value, string $setKey): ?string
    {
        $value = trim(strtolower($value));
        
        $synonyms = [
            'client.cash_or_probono' => [
                'pro bono' => 'probono',
                'pro-bono' => 'probono',
                'free' => 'probono',
                'paid' => 'cash',
                'fee' => 'cash',
            ],
            'client.status' => [
                'inactive' => 'disabled',
                'pending' => 'potential',
                'prospect' => 'potential',
            ],
            'client.power_of_attorney_location' => [
                'archives' => 'archive',
                'filing' => 'archive',
                'client' => 'handed_to_client',
                'returned' => 'handed_to_client',
            ],
            'client.documents_location' => [
                'archives' => 'archive',
                'filing' => 'archive',
                'client' => 'handed_to_client',
                'returned' => 'handed_to_client',
            ],
        ];

        return $synonyms[$setKey][$value] ?? null;
    }

    public function getUsageCount()
    {
        // This will be implemented based on which models use this option value
        // For now, we'll return 0 and implement specific counts in each model
        return 0;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['set_id', 'code', 'label_en', 'label_ar', 'position', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('optionvalue')
            ->setDescriptionForEvent(fn(string $eventName) => "OptionValue was {$eventName}");
    }
}