<?php

namespace Give\API\REST\V3\Support;

use DateTime;

/**
 * Item utilities for WordPress REST API V3
 *
 * Formats DateTime objects and Value Objects for API responses.
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
                // Use DateTime::format('c') instead of mysql_to_rfc3339() because our models
                // always return DateTime objects, not date strings. The 'c' format produces
                // the same ISO 8601 format that mysql_to_rfc3339() would produce for strings.
                $item[$field] = $item[$field]->format('c');
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
