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

        // Trim and collapse whitespace/newlines/tabs to single spaces
        $s = preg_replace('/\s+/u', ' ', trim($s));

        // Remove Arabic diacritics and tatweel
        $diacritics = [
            "\x{064B}", // FATHATAN
            "\x{064C}", // DAMMATAN
            "\x{064D}", // KASRATAN
            "\x{064E}", // FATHA
            "\x{064F}", // DAMMA
            "\x{0650}", // KASRA
            "\x{0651}", // SHADDA
            "\x{0652}", // SUKUN
            "\x{0640}", // TATWEEL
        ];
        $s = str_replace($diacritics, '', $s);

        // Unify common Arabic letter variants
        $map = [
            "\x{0649}" => "\x{064A}", // ى -> ي
            "\x{0629}" => "\x{0647}", // ة -> ه
            "\x{0623}" => "\x{0627}", // أ -> ا
            "\x{0625}" => "\x{0627}", // إ -> ا
            "\x{0622}" => "\x{0627}", // آ -> ا
        ];
        $s = strtr($s, $map);

        return $s;
    }
}
