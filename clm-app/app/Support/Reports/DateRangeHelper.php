<?php

namespace App\Support\Reports;

use Carbon\Carbon;

/**
 * Helper class for date range filtering in reports.
 */
class DateRangeHelper
{
    /**
     * Get the application timezone.
     */
    protected static function getTimezone(): string
    {
        return config('app.timezone', 'UTC');
    }

    /**
     * Resolve date range from request input.
     * 
     * @param string|null $rangeType Preset range type (today, this_week, this_month, custom, etc.)
     * @param string|null $startDate Custom start date (Y-m-d format)
     * @param string|null $endDate Custom end date (Y-m-d format)
     * @return array{start: Carbon, end: Carbon}|null Returns null if no valid range provided
     */
    public static function resolveDateRange(
        ?string $rangeType = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): ?array {
        // If custom dates provided, use them
        if ($startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate, self::getTimezone())->startOfDay(),
                'end' => Carbon::parse($endDate, self::getTimezone())->endOfDay(),
            ];
        }

        // If only range type provided, calculate from preset
        if ($rangeType) {
            return self::getPresetRange($rangeType);
        }

        return null;
    }

    /**
     * Get preset date ranges.
     * 
     * @param string $preset Preset name
     * @return array{start: Carbon, end: Carbon}
     */
    public static function getPresetRange(string $preset): array
    {
        $now = Carbon::now(self::getTimezone());

        return match (strtolower($preset)) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end' => $now->copy()->subDay()->endOfDay(),
            ],
            'this_week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'last_week' => [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end' => $now->copy()->subWeek()->endOfWeek(),
            ],
            'this_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
            ],
            'this_quarter' => [
                'start' => $now->copy()->startOfQuarter(),
                'end' => $now->copy()->endOfQuarter(),
            ],
            'last_quarter' => [
                'start' => $now->copy()->subQuarter()->startOfQuarter(),
                'end' => $now->copy()->subQuarter()->endOfQuarter(),
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            'last_year' => [
                'start' => $now->copy()->subYear()->startOfYear(),
                'end' => $now->copy()->subYear()->endOfYear(),
            ],
            'last_7_days' => [
                'start' => $now->copy()->subDays(7)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'last_30_days' => [
                'start' => $now->copy()->subDays(30)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'last_90_days' => [
                'start' => $now->copy()->subDays(90)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * Get all available preset range options.
     * 
     * @return array<string, string> Array of preset keys => human-readable labels (EN)
     */
    public static function getPresetOptions(): array
    {
        return [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'this_week' => 'This Week',
            'last_week' => 'Last Week',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'this_quarter' => 'This Quarter',
            'last_quarter' => 'Last Quarter',
            'this_year' => 'This Year',
            'last_year' => 'Last Year',
            'last_7_days' => 'Last 7 Days',
            'last_30_days' => 'Last 30 Days',
            'last_90_days' => 'Last 90 Days',
        ];
    }

    /**
     * Validate date range (start must be before end).
     * 
     * @param Carbon $start
     * @param Carbon $end
     * @return bool
     */
    public static function validateDateRange(Carbon $start, Carbon $end): bool
    {
        return $start->lte($end);
    }

    /**
     * Limit date range to maximum allowed span (prevents performance issues).
     * 
     * @param Carbon $start
     * @param Carbon $end
     * @param int $maxDays Maximum days allowed (default: 365)
     * @return array{start: Carbon, end: Carbon}
     */
    public static function limitDateRange(Carbon $start, Carbon $end, int $maxDays = 365): array
    {
        $daysDiff = $start->diffInDays($end);

        if ($daysDiff > $maxDays) {
            // Adjust end date to max days from start
            $end = $start->copy()->addDays($maxDays)->endOfDay();
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Format date range for display.
     * 
     * @param Carbon $start
     * @param Carbon $end
     * @param string $locale Locale for formatting (en/ar)
     * @return string
     */
    public static function formatDateRange(Carbon $start, Carbon $end, string $locale = 'en'): string
    {
        $dateFormat = $locale === 'ar' ? 'd/m/Y' : 'Y-m-d';

        if ($start->isSameDay($end)) {
            return $start->format($dateFormat);
        }

        return $start->format($dateFormat) . ' - ' . $end->format($dateFormat);
    }
}

