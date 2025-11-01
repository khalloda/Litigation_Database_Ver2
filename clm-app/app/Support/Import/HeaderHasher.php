<?php

namespace App\Support\Import;

class HeaderHasher
{
    public static function hash(array $headers): string
    {
        $normalized = array_map(function ($h) {
            return mb_strtolower(trim((string) $h), 'UTF-8');
        }, $headers);
        sort($normalized, SORT_STRING);
        return hash('sha256', json_encode($normalized, JSON_UNESCAPED_UNICODE));
    }
}

