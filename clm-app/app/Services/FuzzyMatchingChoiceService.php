<?php

namespace App\Services;

use App\Models\Lawyer;
use App\Models\Court;
use App\Models\OptionValue;
use Illuminate\Support\Facades\Log;

class FuzzyMatchingChoiceService
{
    /**
     * Get available choices for a failed fuzzy match.
     */
    public function getChoicesForField(string $field, string $searchValue): array
    {
        $choices = [];

        switch ($field) {
            case 'matter_partner_id':
                $choices = $this->getLawyerChoices($searchValue);
                break;

            case 'circuit_secretary':
                $choices = $this->getCircuitSecretaryChoices($searchValue);
                break;

            case 'court_id':
                $choices = $this->getCourtChoices($searchValue);
                break;

            case 'client_capacity_id':
            case 'opponent_capacity_id':
                $choices = $this->getCapacityChoices($searchValue);
                break;

            case 'circuit_name_id':
                $choices = $this->getCircuitChoices($searchValue);
                break;
        }

        return [
            'field' => $field,
            'search_value' => $searchValue,
            'choices' => $choices,
            'can_create' => $this->canCreateNew($field),
            'create_suggestion' => $this->getCreateSuggestion($field, $searchValue)
        ];
    }

    /**
     * Get lawyer choices for fuzzy matching.
     */
    private function getLawyerChoices(string $searchValue): array
    {
        $lawyers = Lawyer::where(function ($q) use ($searchValue) {
            $q->where('lawyer_name_en', 'like', '%' . $searchValue . '%')
                ->orWhere('lawyer_name_ar', 'like', '%' . $searchValue . '%');
        })->limit(10)->get();

        return $lawyers->map(function ($lawyer) {
            return [
                'id' => $lawyer->id,
                'name_ar' => $lawyer->lawyer_name_ar,
                'name_en' => $lawyer->lawyer_name_en,
                'email' => $lawyer->email,
                'title' => $lawyer->title,
                'display' => $lawyer->lawyer_name_ar . ' (' . $lawyer->lawyer_name_en . ')'
            ];
        })->toArray();
    }

    /**
     * Get court choices for fuzzy matching.
     */
    private function getCourtChoices(string $searchValue): array
    {
        $courts = Court::where(function ($q) use ($searchValue) {
            $q->where('court_name_en', 'like', '%' . $searchValue . '%')
                ->orWhere('court_name_ar', 'like', '%' . $searchValue . '%');
        })->limit(10)->get();

        return $courts->map(function ($court) {
            return [
                'id' => $court->id,
                'name_ar' => $court->court_name_ar,
                'name_en' => $court->court_name_en,
                'display' => $court->court_name_ar . ' (' . $court->court_name_en . ')'
            ];
        })->toArray();
    }

    /**
     * Get capacity choices for fuzzy matching.
     */
    private function getCapacityChoices(string $searchValue): array
    {
        // Clean the search value (remove extra whitespace, newlines)
        $searchValue = trim($searchValue);

        // Get all capacity values
        $capacities = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'capacity.type');
        })->get();

        // Filter by similarity (more flexible matching)
        $matches = $capacities->filter(function ($capacity) use ($searchValue) {
            // Exact match
            if ($capacity->label_ar === $searchValue || $capacity->label_en === $searchValue) {
                return true;
            }

            // Contains match
            if (
                strpos($capacity->label_ar, $searchValue) !== false ||
                strpos($capacity->label_en, $searchValue) !== false
            ) {
                return true;
            }

            // Handle Arabic gender variations (e.g., مستأنفة vs مستأنف)
            // Remove feminine endings (ة) and check if the base matches
            $searchBase = rtrim($searchValue, 'ة');
            $capacityBase = rtrim($capacity->label_ar, 'ة');

            if (
                $searchBase === $capacityBase ||
                strpos($capacityBase, $searchBase) !== false ||
                strpos($searchBase, $capacityBase) !== false
            ) {
                return true;
            }

            return false;
        })->take(10)->values(); // Add ->values() to convert to array

        return $matches->map(function ($capacity) {
            return [
                'id' => $capacity->id,
                'label_ar' => $capacity->label_ar,
                'label_en' => $capacity->label_en,
                'display' => $capacity->label_ar . ' (' . $capacity->label_en . ')'
            ];
        })->toArray();
    }

    /**
     * Get circuit choices for fuzzy matching.
     */
    private function getCircuitChoices(string $searchValue): array
    {
        $circuits = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'circuit.name');
        })->where(function ($q) use ($searchValue) {
            $q->where('label_en', 'like', '%' . $searchValue . '%')
                ->orWhere('label_ar', 'like', '%' . $searchValue . '%');
        })->limit(10)->get();

        return $circuits->map(function ($circuit) {
            return [
                'id' => $circuit->id,
                'label_ar' => $circuit->label_ar,
                'label_en' => $circuit->label_en,
                'display' => $circuit->label_ar . ' (' . $circuit->label_en . ')'
            ];
        })->toArray();
    }

    /**
     * Get circuit secretary choices for fuzzy matching.
     */
    private function getCircuitSecretaryChoices(string $searchValue): array
    {
        $secretaries = OptionValue::whereHas('optionSet', function ($q) {
            $q->where('key', 'court.circuit_secretary');
        })->where(function ($q) use ($searchValue) {
            $q->where('label_en', 'like', '%' . $searchValue . '%')
                ->orWhere('label_ar', 'like', '%' . $searchValue . '%');
        })->limit(10)->get();

        return $secretaries->map(function ($secretary) {
            return [
                'id' => $secretary->id,
                'label_ar' => $secretary->label_ar,
                'label_en' => $secretary->label_en,
                'display' => $secretary->label_ar . ' (' . $secretary->label_en . ')'
            ];
        })->toArray();
    }

    /**
     * Check if new values can be created for this field.
     */
    private function canCreateNew(string $field): bool
    {
        return in_array($field, [
            'matter_partner_id',
            'circuit_secretary',
            'court_id',
            'client_capacity_id',
            'opponent_capacity_id',
            'circuit_name_id'
        ]);
    }

    /**
     * Get suggestion for creating new value.
     */
    private function getCreateSuggestion(string $field, string $searchValue): array
    {
        switch ($field) {
            case 'matter_partner_id':
                return [
                    'type' => 'lawyer',
                    'suggestion' => [
                        'lawyer_name_ar' => $searchValue,
                        'lawyer_name_en' => $this->generateEnglishName($searchValue),
                        'email' => $this->generateEmail($searchValue),
                        'title' => 'Associate'
                    ]
                ];

            case 'circuit_secretary':
                return [
                    'type' => 'option_value',
                    'suggestion' => [
                        'label_ar' => $searchValue,
                        'label_en' => $this->generateEnglishName($searchValue),
                        'code' => $this->generateCode($searchValue),
                        'position' => 999
                    ]
                ];

            case 'court_id':
                return [
                    'type' => 'court',
                    'suggestion' => [
                        'court_name_ar' => $searchValue,
                        'court_name_en' => $this->generateEnglishName($searchValue),
                        'is_active' => true
                    ]
                ];

            default:
                return [
                    'type' => 'option_value',
                    'suggestion' => [
                        'label_ar' => $searchValue,
                        'label_en' => $this->generateEnglishName($searchValue)
                    ]
                ];
        }
    }

    /**
     * Generate English name from Arabic.
     */
    private function generateEnglishName(string $arabicName): string
    {
        // Simple transliteration - in a real app, you'd use a proper transliteration service
        $transliterations = [
            'أميرة' => 'Amira',
            'شريف' => 'Sherif',
            'خالد' => 'Khaled',
            'عطية' => 'Attia',
            'هناء' => 'Hana',
            'سالم' => 'Salem'
        ];

        $englishName = $arabicName;
        foreach ($transliterations as $arabic => $english) {
            $englishName = str_replace($arabic, $english, $englishName);
        }

        return $englishName;
    }

    /**
     * Generate email from name.
     */
    private function generateEmail(string $name): string
    {
        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        return $cleanName . '@sarieldin.com';
    }

    /**
     * Generate code from name.
     */
    private function generateCode(string $name): string
    {
        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        return 'secretary_' . $cleanName . '_' . time();
    }
}
