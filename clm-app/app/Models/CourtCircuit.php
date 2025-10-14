<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtCircuit extends Model
{
    use HasFactory;

    protected $table = 'court_circuit';

    protected $fillable = [
        'court_id',
        'circuit_name_id',
        'circuit_serial_id',
        'circuit_shift_id',
    ];

    // Relationships
    public function court()
    {
        return $this->belongsTo(Court::class);
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

    // Accessor for full circuit display (e.g., "Labor 11 (N)")
    public function getFullNameAttribute()
    {
        $name = $this->circuitName ? 
            (app()->getLocale() === 'ar' ? $this->circuitName->label_ar : $this->circuitName->label_en) : '';
        
        $serial = $this->circuitSerial ? 
            (app()->getLocale() === 'ar' ? $this->circuitSerial->label_ar : $this->circuitSerial->label_en) : '';
        
        $shift = $this->circuitShift ? 
            (app()->getLocale() === 'ar' ? $this->circuitShift->label_ar : $this->circuitShift->label_en) : '';

        $result = $name;
        if ($serial) {
            $result .= " {$serial}";
        }
        if ($shift && $shift !== 'Morning') {
            $result .= " ({$shift})";
        }
        
        return $result;
    }
}