<?php

namespace App\Support\View;

class Format
{
    public static function text($value): string
    {
        if ($value === null || $value === '') {
            return '<span class="text-muted">-</span>';
        }
        return '<span dir="auto" class="text-wrap">' . e((string)$value) . '</span>';
    }

    public static function raw($value): string
    {
        if ($value === null || $value === '') {
            return '<span class="text-muted">-</span>';
        }
        return '<span dir="ltr">' . e((string)$value) . '</span>';
    }

    public static function longtext($value, int $previewLength = 150): string
    {
        if ($value === null || $value === '') {
            return '<span class="text-muted">-</span>';
        }

        $cleaned = trim(strip_tags((string)$value));
        if (mb_strlen($cleaned) <= $previewLength) {
            return '<div dir="auto" class="text-wrap text-break">' . nl2br(e($cleaned)) . '</div>';
        }

        // Will be handled by _longtext partial
        $id = 'longtext-' . uniqid();
        $preview = mb_substr($cleaned, 0, $previewLength);
        $full = $cleaned;

        return view('cases.partials._longtext', compact('id', 'preview', 'full'))->render();
    }

    public static function date($value): string
    {
        if (!$value) {
            return '<span class="text-muted">-</span>';
        }
        try {
            if (is_string($value)) {
                $value = \Carbon\Carbon::parse($value);
            }
            return '<span dir="ltr">' . e($value->format('Y-m-d')) . '</span>';
        } catch (\Exception $e) {
            return '<span class="text-muted">-</span>';
        }
    }

    public static function datetime($value): string
    {
        if (!$value) {
            return '<span class="text-muted">-</span>';
        }
        try {
            if (is_string($value)) {
                $value = \Carbon\Carbon::parse($value);
            }
            return '<span dir="ltr">' . e($value->format('Y-m-d H:i:s')) . '</span>';
        } catch (\Exception $e) {
            return '<span class="text-muted">-</span>';
        }
    }

    public static function money($value): string
    {
        if ($value === null || $value === '' || $value === 0) {
            return '<span class="text-muted">-</span>';
        }
        $formatted = number_format((float)$value, 2);
        return '<span dir="ltr">' . e($formatted) . ' <small class="text-muted">EGP</small></span>';
    }

    public static function boolean($value): string
    {
        $value = (bool)$value;
        $label = $value ? __('app.yes') : __('app.no');
        $badgeClass = $value ? 'bg-success' : 'bg-secondary';
        return '<span class="badge ' . $badgeClass . '">' . e($label) . '</span>';
    }

    public static function person($value): string
    {
        return self::text($value);
    }

    /**
     * Format foreign key with link and label
     *
     * @param mixed $model The case model instance
     * @param string $relation The relation method name
     * @param mixed $id The ID value
     * @param string $format Format type (e.g., 'fk:option:circuitName')
     * @return string
     */
    public static function fk($model, string $relation, $id, string $format): string
    {
        if (!$id) {
            return '<span class="text-muted">-</span>';
        }

        $related = null;
        $label = null;
        $routeName = null;

        try {
            // Handle special cases where relation name differs or doesn't exist
            if (str_contains($format, 'fk:user')) {
                // For created_by/updated_by, manually load User (no relation defined)
                $user = \App\Models\User::find($id);
                if ($user) {
                    $related = $user;
                    $label = $user->name ?: $user->email;
                }
            } elseif ($model && method_exists($model, $relation)) {
                // Try to load the relation
                $related = $model->$relation;
            }

            // If relation wasn't loaded and format indicates OptionValue, try loading directly
            if (!$related && str_contains($format, 'fk:option')) {
                $optionValue = \App\Models\OptionValue::find($id);
                if ($optionValue) {
                    $related = $optionValue;
                }
            }

            // Get label based on relation type
            if ($related) {
                // OptionValue relations (circuitName, matterCategory, etc.)
                if ($related instanceof \App\Models\OptionValue) {
                    $label = app()->getLocale() === 'ar' ? $related->label_ar : $related->label_en;
                    if (!$label || trim($label) === '') {
                        $label = $related->label_en ?: $related->label_ar;
                    }
                    // If still no label, try code as fallback
                    if (!$label || trim($label) === '') {
                        $label = $related->code ?: ('ID: ' . $related->id);
                    }
                }
                // Court relations
                elseif ($related instanceof \App\Models\Court) {
                    $label = app()->getLocale() === 'ar' ? $related->court_name_ar : $related->court_name_en;
                    if (!$label) {
                        $label = $related->court_name_en ?: $related->court_name_ar;
                    }
                    $routeName = 'courts.show';
                }
                // Client relations
                elseif ($related instanceof \App\Models\Client) {
                    $label = $related->client_name_ar ?: $related->client_name_en;
                    if (!$label) {
                        $label = $related->client_name_en ?: $related->client_name_ar;
                    }
                    $routeName = 'clients.show';
                }
                // Opponent relations
                elseif ($related instanceof \App\Models\Opponent) {
                    $label = app()->getLocale() === 'ar' ? $related->opponent_name_ar : $related->opponent_name_en;
                    if (!$label) {
                        $label = $related->opponent_name_en ?: $related->opponent_name_ar;
                    }
                    $routeName = 'opponents.show';
                }
                // Lawyer relations
                elseif ($related instanceof \App\Models\Lawyer) {
                    $label = $related->lawyer_name_ar ?: $related->lawyer_name_en;
                    if (!$label) {
                        $label = $related->lawyer_name_en ?: $related->lawyer_name_ar;
                    }
                    $routeName = 'lawyers.show';
                }
                // User relations
                elseif ($related instanceof \App\Models\User) {
                    $label = $related->name ?: $related->email;
                    // No route for users for now, just show name/email
                }
                // EngagementLetter relations
                elseif ($related instanceof \App\Models\EngagementLetter) {
                    $label = $related->client_name ?: ('ID: ' . $related->id);
                    $routeName = 'engagement-letters.show';
                }
            }
        } catch (\Exception $e) {
            // If relation fails, try loading OptionValue directly as fallback
            if (str_contains($format, 'fk:option')) {
                try {
                    $optionValue = \App\Models\OptionValue::find($id);
                    if ($optionValue) {
                        $label = app()->getLocale() === 'ar' ? $optionValue->label_ar : $optionValue->label_en;
                        if (!$label || trim($label) === '') {
                            $label = $optionValue->label_en ?: $optionValue->label_ar;
                        }
                        if (!$label || trim($label) === '') {
                            $label = $optionValue->code ?: ('ID: ' . $optionValue->id);
                        }
                    }
                } catch (\Exception $e2) {
                    // Ignore
                }
            }
        }

        if (!$label || trim($label) === '') {
            $label = 'ID: ' . $id;
        }

        $html = '';
        if ($routeName && $related) {
            $html .= '<a href="' . route($routeName, $related->id) . '" dir="auto">' . e($label) . '</a>';
        } else {
            $html .= '<span dir="auto">' . e($label) . '</span>';
        }

        // Always show ID in muted text
        $html .= ' <small class="text-muted">(ID: ' . $id . ')</small>';

        return $html;
    }
}
