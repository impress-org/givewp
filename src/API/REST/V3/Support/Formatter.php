<?php

namespace Give\API\REST\V3\Support;

use DateTime;

/**
 * Formatter for WordPress REST API V3
 *
 * This class handles formatting of various data types for API responses,
 * ensuring consistency across all endpoints and compatibility with WordPress standards.
 *
 * Features:
 * - Automatic detection and formatting of date fields
 * - Automatic detection and conversion of Value Objects
 * - Batch formatting for arrays of data
 *
 * Format: Y-m-d\TH:i:sP (e.g., "2023-12-25T14:30:00+00:00")
 *
 * References:
 * - WordPress mysql_to_rfc3339 function: https://developer.wordpress.org/reference/functions/mysql_to_rfc3339/
 * - RFC3339 Specification: https://tools.ietf.org/html/rfc3339
 *
 * @unreleased
 */
class Formatter
{
    /**
     * Format a single date for API response
     *
     * @param mixed $date DateTime object, string, or null
     */
    public static function formatDate($date): ?string
    {
        if ($date === null) {
            return null;
        }

        if ($date instanceof DateTime) {
            return $date->format('c');
        }

        if (is_string($date)) {
            // phpcs:disable -- WordPress core function mysql_to_rfc3339 is intentionally used for compatibility
            return mysql_to_rfc3339($date);
            // phpcs:enable
        }

        return null;
    }

    /**
     * Format multiple dates in an array
     */
    public static function formatDates(array $data, array $dateFields = []): array
    {
        foreach ($data as $field => $value) {
            if (empty($dateFields) || in_array($field, $dateFields, true)) {
                if (self::isDateValue($value)) {
                    $data[$field] = self::formatDate($value);
                }
            }
        }

        return $data;
    }

    /**
     * Format value objects in an array
     */
    public static function formatValueObjects(array $data, array $valueObjectFields = []): array
    {
        foreach ($data as $field => $value) {
            if (empty($valueObjectFields) || in_array($field, $valueObjectFields, true)) {
                if (self::isValueObject($value)) {
                    $data[$field] = $value->getValue();
                }
            }
        }

        return $data;
    }

    /**
     * Format all data types for API response automatically
     *
     * This method automatically detects and formats date fields and value objects
     * without requiring manual specification of field names.
     */
    public static function formatAll(array $data, array $dateFields = [], array $valueObjectFields = []): array
    {
        // If no specific fields provided, auto-detect in a single loop
        if (empty($dateFields) && empty($valueObjectFields)) {
            foreach ($data as $field => $value) {
                if (self::isDateValue($value)) {
                    $data[$field] = self::formatDate($value);
                } elseif (self::isValueObject($value)) {
                    $data[$field] = $value->getValue();
                }
            }
            return $data;
        }

        // Use specific field lists if provided
        $data = self::formatDates($data, $dateFields);
        $data = self::formatValueObjects($data, $valueObjectFields);

        return $data;
    }

    /**
     * Check if a value looks like a date
     */
    private static function isDateValue($value): bool
    {
        // DateTime objects are always dates
        if ($value instanceof DateTime) {
            return true;
        }

        // Check if string looks like a date
        if (is_string($value)) {
            // Common date patterns
            $datePatterns = [
                '/^\d{4}-\d{2}-\d{2}$/',                    // Y-m-d
                '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', // Y-m-d H:i:s
                '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',  // ISO 8601
                '/^\d{2}\/\d{2}\/\d{4}$/',                  // m/d/Y
                '/^\d{2}-\d{2}-\d{4}$/',                    // m-d-Y
            ];

            foreach ($datePatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a value is a value object
     */
    private static function isValueObject($value): bool
    {
        return is_object($value) && method_exists($value, 'getValue');
    }
}
