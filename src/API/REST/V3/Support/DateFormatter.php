<?php

namespace Give\API\REST\V3\Support;

use DateTime;

/**
 * DateFormatter for WordPress REST API V3
 *
 * This class formats dates to follow the WordPress REST API standard format (RFC3339/ISO 8601).
 * WordPress uses this format for all date fields in REST API responses to ensure consistency
 * across all endpoints and compatibility with JavaScript Date objects.
 *
 * This implementation uses the WordPress core function `mysql_to_rfc3339()` for string dates,
 * ensuring compatibility with WordPress standards. Search for prepare_date_response on the
 * WordPress Posts Controller (class-wp-rest-posts-controller.php) to see how it's used in WP core:
 * https://github.com/WordPress/WordPress/blob/master/wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php
 *
 * Format: Y-m-d\TH:i:sP (e.g., "2023-12-25T14:30:00+00:00")
 *
 * References:
 * - WordPress mysql_to_rfc3339 function: https://developer.wordpress.org/reference/functions/mysql_to_rfc3339/
 * - RFC3339 Specification: https://tools.ietf.org/html/rfc3339
 *
 * @unreleased
 */
class DateFormatter
{
    /**
     * @unreleased
     */
    public static function formatDateForResponse($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        if ($date instanceof DateTime) {
            return $date->format('Y-m-d\TH:i:sP');
        }

        if (is_string($date)) {
            // phpcs:disable -- WordPress core function mysql_to_rfc3339 is intentionally used for compatibility
            return mysql_to_rfc3339($date);
            // phpcs:enable
        }

        return null;
    }

    /**
     * @unreleased
     */
    public static function formatDatesForResponse(array $data, array $dateFields = ['createdAt', 'updatedAt', 'dateCreated', 'dateUpdated', 'renewsAt']): array
    {
        foreach ($dateFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = self::formatDateForResponse($data[$field]);
            }
        }

        return $data;
    }
}
