<?php

namespace Give\API\REST\V3\Support;

use DateTime;

/**
 * Item utilities for WordPress REST API V3
 *
 * Formats DateTime objects for API responses using WordPress-compatible format.
 * Date formatting uses WordPress mysql_to_rfc3339() function for full compatibility.
 * Only DateTime objects are supported for date formatting.
 *
 * @since 4.9.0
 */
class Item
{
    /**
     * @since 4.9.0
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
     * @since 4.9.0
     */
    private static function isDateTimeObject($value): bool
    {
        return $value instanceof DateTime;
    }
}
