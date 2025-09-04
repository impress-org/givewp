<?php

namespace Give\API\REST\V3\Support;

use DateTime;

/**
 * Item utilities for WordPress REST API V3
 *
 * This class handles all item formatting functionality including data formatting,
 * ensuring consistency across all endpoints and compatibility with WordPress standards.
 *
 * Features:
 * - Data formatting for API responses
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
class Item
{
    /**
     * Format item data for API response with automatic detection
     *
     * @param array $item WordPress representation of the item.
     * @param array $dateFields
     * @param array $valueObjectFields
     * @return array
     */
    public static function formatForResponse(array $item, array $dateFields = [], array $valueObjectFields = []): array
    {
        // If no specific fields provided, auto-detect in a single loop
        if (empty($dateFields) && empty($valueObjectFields)) {
            foreach ($item as $field => $value) {
                if (self::isDateValue($value)) {
                    $item[$field] = self::formatDateForResponse($value);
                } elseif (self::isValueObject($value)) {
                    $item[$field] = $value->getValue();
                }
            }
            return $item;
        }

        // Use specific field lists if provided
        $item = self::formatDatesForResponse($item, $dateFields);
        $item = self::formatValueObjectsForResponse($item, $valueObjectFields);

        return $item;
    }

    /**
     * Format a single date for API response
     *
     * @param mixed $date DateTime object, string, or null
     * @return string|null ISO 8601 formatted date string or null
     */
    public static function formatDateForResponse($date): ?string
    {
        if ($date === null) {
            return null;
        }

        // Format DateTime to ISO 8601 string (e.g., "2023-12-25T14:30:00+00:00")
        if ($date instanceof DateTime) {
            return $date->format('c');
        }

        // Format String to ISO 8601 string (e.g., "2023-12-25T14:30:00+00:00")
        if (is_string($date)) {
            // phpcs:disable -- WordPress core function mysql_to_rfc3339 is intentionally used for compatibility
            return mysql_to_rfc3339($date);
            // phpcs:enable
        }

        return null;
    }

    /**
     * Format multiple dates in an array
     *
     * @param array $item WordPress representation of the item.
     * @param array $dateFields
     * @return array
     */
    public static function formatDatesForResponse(array $item, array $dateFields = []): array
    {
        foreach ($item as $field => $value) {
            if (empty($dateFields) || in_array($field, $dateFields, true)) {
                if (self::isDateValue($value)) {
                    $item[$field] = self::formatDateForResponse($value);
                }
            }
        }

        return $item;
    }

    /**
     * Format value objects in an array
     *
     * @param array $item WordPress representation of the item.
     * @param array $valueObjectFields
     * @return array
     */
    public static function formatValueObjectsForResponse(array $item, array $valueObjectFields = []): array
    {
        foreach ($item as $field => $value) {
            if (empty($valueObjectFields) || in_array($field, $valueObjectFields, true)) {
                if (self::isValueObject($value)) {
                    $item[$field] = $value->getValue();
                }
            }
        }

        return $item;
    }

    /**
     * Check if a value looks like a date
     *
     * @param mixed $value
     * @return bool
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
     *
     * @param mixed $value
     * @return bool
     */
    private static function isValueObject($value): bool
    {
        return is_object($value) && method_exists($value, 'getValue');
    }
}
