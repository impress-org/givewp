<?php

namespace Give\API\REST\V3\Support;

use DateTime;

/**
 * Item utilities for WordPress REST API V3
 *
 * Formats DateTime objects and Value Objects for API responses.
 * Date formatting uses WordPress mysql_to_rfc3339() function for full compatibility.
 * Only DateTime objects are supported for date formatting.
 *
 * @unreleased
 */
class Item
{
    /**
     * @unreleased
     */
    public static function formatForResponse(array $item, array $dateFields, array $valueObjectFields): array
    {
        $item = self::formatDatesForResponse($item, $dateFields);
        $item = self::formatValueObjectsForResponse($item, $valueObjectFields);

        return $item;
    }

    /**
     * @unreleased
     */
    public static function formatDatesForResponse(array $item, array $dateFields): array
    {
        foreach ($dateFields as $field) {
            if (isset($item[$field]) && self::isDateTimeObject($item[$field])) {
                // Convert DateTime to WordPress-compatible ISO 8601 format.
                // PHP's DateTime::format('c') includes timezone (e.g., "2025-09-02T20:27:02+00:00"),
                // but WordPress removes the timezone for consistency across all endpoints.
                // WordPress format: "2025-09-02T20:27:02" (without timezone)
                // Using mysql_to_rfc3339() ensures compatibility with WordPress date formatting standards.

                // phpcs:disable -- WordPress core function mysql_to_rfc3339 is intentionally used for compatibility
                $item[$field] = mysql_to_rfc3339($item[$field]->format('c'));
                // phpcs:enable
            }
        }

        return $item;
    }

    /**
     * @unreleased
     */
    public static function formatValueObjectsForResponse(array $item, array $valueObjectFields): array
    {
        foreach ($valueObjectFields as $field) {
            if (isset($item[$field]) && self::isValueObject($item[$field])) {
                $item[$field] = $item[$field]->getValue();
            }
        }

        return $item;
    }

    /**
     * @unreleased
     */
    private static function isDateTimeObject($value): bool
    {
        return $value instanceof DateTime;
    }

    /**
     * @unreleased
     */
    private static function isValueObject($value): bool
    {
        return is_object($value) && method_exists($value, 'getValue');
    }
}
