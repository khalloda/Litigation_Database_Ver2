<?php

namespace App\Support;

class TextNormalizer
{
    /**
     * Normalize input text for stable comparisons across Arabic/English values.
     * - Trim, collapse internal whitespace
     * - Normalize Unicode (NFC)
     * - Remove Arabic diacritics and tatweel
     * - Unify common Arabic letter variants (ي/ى, ة/ه)
     */
    public function normalize(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $s = (string) $text;

        // Normalize unicode to NFC if intl extension available
        if (class_exists('Normalizer')) {
            $s = \Normalizer::normalize($s, \Normalizer::FORM_C);
        }

        // Convert various non-breaking spaces to normal spaces
        $s = str_replace(["\u{00A0}", "\u{202F}", "\u{2060}"], ' ', $s);

        // Strip zero-width/formatting marks (ZWNJ, ZWJ, LRM, RLM, ALM, FEFF)
        $s = preg_replace('/[\x{200C}\x{200D}\x{200E}\x{200F}\x{061C}\x{FEFF}]/u', '', $s);

        // Trim and collapse whitespace/newlines/tabs to single spaces
        $s = preg_replace('/\s+/u', ' ', trim($s));

        // Remove all combining marks (\p{M}) and tatweel
        $s = preg_replace('/[\p{M}\x{0640}]+/u', '', $s);

        // Unify common Arabic letter variants
        // ى -> ي
        $s = strtr($s, ['ى' => 'ي']);
        // Hamza variants -> bare alif (أ إ آ ؤ ئ ء -> ا)
        $s = preg_replace('/[أإآؤئء]/u', 'ا', $s);

        return $s;
    }
}
