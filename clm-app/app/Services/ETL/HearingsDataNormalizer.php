<?php

namespace App\Services\ETL;

/**
 * Hearings Data Normalizer Service
 * 
 * Centralized parsing and normalization helpers for hearings import.
 * Handles text cleanup, date parsing, boolean normalization, and circuit parsing.
 */
class HearingsDataNormalizer
{
    /**
     * Clean and normalize text string.
     * 
     * Removes Excel artifacts, zero-width characters, direction marks,
     * collapses whitespace, normalizes Arabic digits to ASCII.
     * 
     * @param string|null $value
     * @return string|null
     */
    public function cleanString(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Remove Excel artifacts
        $value = str_replace('_x000D_', '', $value);
        $value = str_replace('_x000d_', '', $value);

        // Remove zero-width and direction marks (U+200B-U+200D, U+FEFF, U+200E, U+200F)
        $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{200E}\x{200F}]/u', '', $value);

        // Normalize Arabic digits (٠-٩) to English (0-9)
        $arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $value = str_replace($arabicDigits, $englishDigits, $value);

        // Collapse multiple whitespace to single space
        $value = preg_replace('/\s+/', ' ', $value);

        // Trim leading/trailing whitespace
        $value = trim($value);

        return $value ?: null;
    }

    /**
     * Parse date from various formats to YYYY-MM-DD.
     * 
     * Accepts: DD/MM/YYYY, YYYY/MM/DD, YYYY-MM-DD
     * 
     * @param string|null $value
     * @return string|null Date in YYYY-MM-DD format or null if invalid
     */
    public function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        // Try DD/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
            $day = (int)$matches[1];
            $month = (int)$matches[2];
            $year = (int)$matches[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Try YYYY/MM/DD
        if (preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $value, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Try YYYY-MM-DD (already normalized)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        return null; // Invalid date
    }

    /**
     * Parse date with raw value tracking.
     * 
     * Returns array with 'date' (YYYY-MM-DD or null) and 'raw' (original value or null).
     * 
     * Rules:
     * - Valid → date = 'Y-m-d', raw = NULL
     * - Invalid/blank → date = NULL, raw = trim($original)
     * 
     * @param string|null $value
     * @return array{date: ?string, raw: ?string}
     */
    public function parseDateWithRaw(?string $value): array
    {
        if (empty($value)) {
            return ['date' => null, 'raw' => null];
        }

        $trimmed = trim($value);
        $parsed = $this->parseDate($trimmed);

        if ($parsed) {
            // Valid date → date = parsed, raw = NULL
            return ['date' => $parsed, 'raw' => null];
        } else {
            // Invalid/blank → date = NULL, raw = trimmed original
            return ['date' => null, 'raw' => $trimmed ?: null];
        }
    }

    /**
     * Parse boolean value.
     * 
     * Normalizes TRUE/FALSE, 1/0, Yes/No, Y/N (case-insensitive) to boolean.
     * 
     * @param mixed $value
     * @return bool
     */
    public function parseBoolean($value): bool
    {
        if (empty($value)) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string)$value));
        $truthy = ['true', '1', 'yes', 'y', 'on'];

        return in_array($normalized, $truthy);
    }

    /**
     * Parse circuit compound field with three-tier regex approach.
     * 
     * Priority:
     * 1. Use split columns if available (nameCol, notesCol, serialCol)
     * 2. Strict regex: "2 - رول (36) شق مستعجل"
     * 3. Common regex: "40 عمال" or "5 - رول (36) اقتصادي"
     * 4. Fallback: Extract leading digits as serial, remainder as name
     * 
     * @param string|null $circuitValue Original compound field value
     * @param string|null $nameCol Split column: circuit name
     * @param string|null $notesCol Split column: circuit notes
     * @param string|null $serialCol Split column: circuit serial
     * @return array{circuit: ?string, notes: ?string, serial: ?int}
     */
    public function parseCircuit(
        ?string $circuitValue,
        ?string $nameCol = null,
        ?string $notesCol = null,
        ?string $serialCol = null
    ): array {
        // Priority 1: Use split columns if available
        if ($nameCol && trim($nameCol)) {
            return [
                'circuit' => $this->cleanString($nameCol),
                'notes' => $this->cleanString($notesCol),
                'serial' => $this->parseInt($serialCol),
            ];
        }

        // Priority 2: Parse compound field (normalize Arabic digits first)
        if (empty($circuitValue)) {
            return ['circuit' => null, 'notes' => null, 'serial' => null];
        }

        // Normalize Arabic digits before regex
        $normalized = $this->cleanString($circuitValue);
        if (!$normalized) {
            return ['circuit' => null, 'notes' => null, 'serial' => null];
        }

        // Tier 1: Strict regex - "2 - رول (36) شق مستعجل"
        // Pattern: ^(\d+)\s*-\s*(.+?)\s+([^\d]+)$
        if (preg_match('/^(\d+)\s*-\s*(.+?)\s+([^\d]+)$/u', $normalized, $matches)) {
            return [
                'circuit' => $this->cleanString($matches[3]),
                'notes' => $this->cleanString($matches[2]),
                'serial' => (int)$matches[1],
            ];
        }

        // Tier 2: Common regex - "40 عمال" or "5 - رول (36) اقتصادي"
        // Pattern: ^(\d+)\s*(?:-|\s+)?\s*(?:رول\s*\((\d+)\)\s*)?(.+)$
        if (preg_match('/^(\d+)\s*(?:-|\s+)?\s*(?:رول\s*\((\d+)\)\s*)?(.+)$/u', $normalized, $matches)) {
            $serial = (int)$matches[1];
            $rollNumber = isset($matches[2]) ? (int)$matches[2] : null;
            $name = $this->cleanString($matches[3]);
            
            // Build notes if roll number exists
            $notes = $rollNumber ? "رول ({$rollNumber})" : null;
            
            return [
                'circuit' => $name,
                'notes' => $notes,
                'serial' => $serial,
            ];
        }

        // Tier 3: Fallback - Extract leading digits as serial, remainder as name
        // Pattern: ^(\d+)\s*(.*)$
        if (preg_match('/^(\d+)\s*(.*)$/u', $normalized, $matches)) {
            return [
                'circuit' => $this->cleanString($matches[2] ?: $normalized),
                'notes' => null,
                'serial' => (int)$matches[1],
            ];
        }

        // Final fallback: return as-is
        return [
            'circuit' => $this->cleanString($normalized),
            'notes' => null,
            'serial' => null,
        ];
    }

    /**
     * Parse integer value.
     * 
     * @param mixed $value
     * @return int|null
     */
    public function parseInt($value): ?int
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return null;
        }

        $value = trim((string)$value);
        $int = filter_var($value, FILTER_VALIDATE_INT);

        return $int !== false ? $int : null;
    }

    /**
     * Parse external ID from source file.
     * 
     * Reads the file's id column into external_id (string). If blank → NULL.
     * Never coerces external_id into production PK.
     * 
     * @param mixed $value
     * @return string|null
     */
    public function parseExternalId($value): ?string
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return null;
        }

        $trimmed = trim((string)$value);
        return $trimmed ?: null;
    }

    /**
     * Synthesize a deterministic external_id from row data.
     * 
     * Creates a stable fingerprint using fields that won't change during retries:
     * source_file + source_row + matter_id + date_raw + court + procedure + decision
     * 
     * @param string $sourceFile
     * @param int $sourceRow
     * @param array $payload Row data after transformation
     * @return string Deterministic external_id (format: HRN-{16-char-hash})
     */
    public function synthesizeExternalId(string $sourceFile, int $sourceRow, array $payload): string
    {
        $fingerprint = implode('|', [
            basename($sourceFile),
            (string)$sourceRow,
            (string)($payload['matter_id'] ?? ''),
            (string)($payload['date_raw'] ?? ''),
            (string)($payload['court'] ?? ''),
            (string)($payload['procedure'] ?? ''),
            mb_substr((string)($payload['decision'] ?? ''), 0, 100), // Limit decision length for stability
        ]);

        $hash = substr(sha1($fingerprint), 0, 16);
        return 'HRN-' . strtoupper($hash);
    }
}

