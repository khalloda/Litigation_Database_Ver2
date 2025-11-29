<?php

namespace App\Support\Reports;

use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Helper class for formatting report data (dates, numbers, text).
 */
class ReportFormatter
{
    /**
     * Format a date for display in reports.
     * 
     * @param mixed $date Can be Carbon, DateTime, string, or null
     * @param string $locale Locale (en/ar)
     * @param string $format Format type (short, medium, long, datetime)
     * @return string
     */
    public static function formatDate($date, string $locale = 'en', string $format = 'medium'): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        // Convert to Carbon if needed
        if (!$date instanceof Carbon) {
            try {
                $date = Carbon::parse($date);
            } catch (\Exception $e) {
                return '—';
            }
        }

        // Set locale for Arabic formatting
        if ($locale === 'ar') {
            Carbon::setLocale('ar');
        }

        return match ($format) {
            'short' => $date->format($locale === 'ar' ? 'd/m/Y' : 'Y-m-d'),
            'medium' => $date->format($locale === 'ar' ? 'd/m/Y' : 'd M Y'),
            'long' => $date->format($locale === 'ar' ? 'd F Y' : 'F d, Y'),
            'datetime' => $date->format($locale === 'ar' ? 'd/m/Y H:i' : 'Y-m-d H:i'),
            'time' => $date->format('H:i'),
            default => $date->format($locale === 'ar' ? 'd/m/Y' : 'Y-m-d'),
        };
    }

    /**
     * Format a number/amount for display.
     * 
     * @param mixed $number
     * @param int $decimals Number of decimal places
     * @param string|null $currency Currency symbol (optional)
     * @param string $locale Locale (en/ar)
     * @return string
     */
    public static function formatNumber($number, int $decimals = 2, ?string $currency = null, string $locale = 'en'): string
    {
        if ($number === null || $number === '') {
            return '—';
        }

        $number = (float) $number;
        $formatted = number_format($number, $decimals, '.', ',');

        if ($currency) {
            return $locale === 'ar' 
                ? $formatted . ' ' . $currency  // Arabic: number then currency
                : $currency . ' ' . $formatted; // English: currency then number
        }

        return $formatted;
    }

    /**
     * Format currency amount.
     * 
     * @param mixed $amount
     * @param string $currencyCode Currency code (EGP, USD, etc.)
     * @param string $locale Locale (en/ar)
     * @return string
     */
    public static function formatCurrency($amount, string $currencyCode = 'EGP', string $locale = 'en'): string
    {
        $symbol = match (strtoupper($currencyCode)) {
            'EGP' => 'ج.م' ?? 'EGP',
            'USD' => '$',
            'EUR' => '€',
            default => $currencyCode,
        };

        return self::formatNumber($amount, 2, $symbol, $locale);
    }

    /**
     * Format text with truncation and ellipsis.
     * 
     * @param string|null $text
     * @param int $maxLength Maximum length
     * @param string $suffix Suffix for truncated text
     * @return string
     */
    public static function truncateText(?string $text, int $maxLength = 100, string $suffix = '...'): string
    {
        if ($text === null || $text === '') {
            return '—';
        }

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return mb_substr($text, 0, $maxLength) . $suffix;
    }

    /**
     * Join array parts with separator, filtering out empty values.
     * 
     * @param array $parts
     * @param string $separator
     * @return string
     */
    public static function joinParts(array $parts, string $separator = ' - '): string
    {
        $filtered = array_values(array_filter(array_map(function ($value) {
            return $value !== null && $value !== '' ? trim((string) $value) : null;
        }, $parts)));

        return !empty($filtered) ? implode($separator, $filtered) : '—';
    }

    /**
     * Format boolean as text.
     * 
     * @param mixed $value
     * @param string $locale Locale (en/ar)
     * @return string
     */
    public static function formatBoolean($value, string $locale = 'en'): string
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        if ($locale === 'ar') {
            return $bool ? 'نعم' : 'لا';
        }

        return $bool ? 'Yes' : 'No';
    }

    /**
     * Format percentage.
     * 
     * @param mixed $value Value between 0-100
     * @param int $decimals Decimal places
     * @return string
     */
    public static function formatPercentage($value, int $decimals = 1): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return number_format((float) $value, $decimals) . '%';
    }

    /**
     * Format file size.
     * 
     * @param int $bytes File size in bytes
     * @return string
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Format duration (e.g., "5 days", "2 months").
     * 
     * @param Carbon $start
     * @param Carbon|null $end End date (null = now)
     * @param string $locale Locale (en/ar)
     * @return string
     */
    public static function formatDuration(Carbon $start, ?Carbon $end = null, string $locale = 'en'): string
    {
        $end = $end ?? Carbon::now();

        $days = $start->diffInDays($end);
        $months = $start->diffInMonths($end);
        $years = $start->diffInYears($end);

        if ($years > 0) {
            return $years . ' ' . ($locale === 'ar' ? 'سنة' : 'year' . ($years > 1 ? 's' : ''));
        }

        if ($months > 0) {
            return $months . ' ' . ($locale === 'ar' ? 'شهر' : 'month' . ($months > 1 ? 's' : ''));
        }

        return $days . ' ' . ($locale === 'ar' ? 'يوم' : 'day' . ($days > 1 ? 's' : ''));
    }

    /**
     * Escape HTML for safe display.
     * 
     * @param string|null $text
     * @return string
     */
    public static function escapeHtml(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

