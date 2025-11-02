<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class NameNormalizer
{
    private const AR_DIACRITICS_REGEX = '/[\x{064B}-\x{0652}]/u';
    private const TATWEEL = "\x{0640}";

    public function normalize(string $name): array
    {
        $s = $name ?? '';

        if (class_exists(\Normalizer::class)) {
            $s = normalizer_is_normalized($s, \Normalizer::FORM_C) ? $s : normalizer_normalize($s, \Normalizer::FORM_C);
        } else {
            if (config('fuzzy.debug.log_env_fallbacks')) Log::warning('intl extension missing; NFC skipped');
        }

        $s = $this->standardizeDigits($s);
        $s = $this->removePunctuation($s);
        $s = $this->collapseWhitespace($s);

        $script = $this->detectScript($s);

        if ($script !== 'latin') $s = $this->normalizeArabic($s);
        if ($script !== 'ar')    $s = $this->normalizeLatin($s);

        $s = $this->removeStopwords($s, config('fuzzy.stopwords.en', []));
        $s = $this->removeStopwords($s, config('fuzzy.stopwords.ar', []));

        $s = $this->collapseWhitespace($s);

        $tokens = array_values(array_filter(explode(' ', $s), fn($t) => mb_strlen($t, 'UTF-8') > 1));
        $first = $tokens[0] ?? null;
        $last  = $tokens ? end($tokens) : null;

        $latinKey = null;
        if ($this->isLatinOnly($s) && config('fuzzy.features.phonetics')) {
            $latinKey = $this->latinKey($s);
        } elseif ($this->isLatinOnly($s)) {
            if (config('fuzzy.debug.log_env_fallbacks')) Log::info('metaphone disabled; phonetics feature off');
        }

        $script = $this->isLatinOnly($s) ? 'latin' : ($this->isArabicOnly($s) ? 'ar' : 'mixed');

        return [
            'normalized'   => $s,
            'first_token'  => $first,
            'last_token'   => $last,
            'token_count'  => count($tokens),
            'latin_key'    => $latinKey,
            'script'       => $script,
        ];
    }

    private function standardizeDigits(string $s): string
    {
        $ar = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $eu = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($ar, $eu, $s);
    }

    private function removePunctuation(string $s): string
    {
        return preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $s) ?? $s;
    }

    private function collapseWhitespace(string $s): string
    {
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return trim($s);
    }

    private function detectScript(string $s): string
    {
        $hasAr = preg_match('/\p{Arabic}/u', $s) === 1;
        $hasLa = preg_match('/[A-Za-z]/u', $s) === 1;
        if ($hasAr && $hasLa) return 'mixed';
        if ($hasAr) return 'ar';
        if ($hasLa) return 'latin';
        return 'mixed';
    }

    private function normalizeArabic(string $s): string
    {
        $s = preg_replace(self::AR_DIACRITICS_REGEX, '', $s) ?? $s;
        $s = str_replace(self::TATWEEL, '', $s);
        $s = str_replace(['أ', 'إ', 'آ', 'ٱ'], 'ا', $s);
        $s = str_replace('ى', 'ي', $s);
        // Keep Ta Marbuta as-is
        return $s;
    }

    private function normalizeLatin(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('/\b([a-z])\.\b/u', '$1', $s) ?? $s;
        if (extension_loaded('iconv')) {
            $fold = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($fold !== false) $s = $fold;
        } else {
            if (config('fuzzy.debug.log_env_fallbacks')) Log::warning('iconv missing; ASCII-fold skipped');
        }
        return $s;
    }

    private function removeStopwords(string $s, array $stop): string
    {
        if (!$stop) return $s;
        $pattern = '/(?<!\p{L})(?:' . implode('|', array_map(fn($w) => preg_quote($w, '/'), $stop)) . ')(?!\p{L})/u';
        return preg_replace($pattern, ' ', $s) ?? $s;
    }

    private function isLatinOnly(string $s): bool
    {
        return $s !== '' && preg_match('/^[\p{Latin}\p{N}\s]+$/u', $s) === 1;
    }
    private function isArabicOnly(string $s): bool
    {
        return $s !== '' && preg_match('/^[\p{Arabic}\p{N}\s]+$/u', $s) === 1;
    }

    private function latinKey(string $s): ?string
    {
        $tokens = array_values(array_filter(explode(' ', $s)));
        return implode(' ', array_map(fn($t) => metaphone($t) ?: $t, $tokens));
    }
}
