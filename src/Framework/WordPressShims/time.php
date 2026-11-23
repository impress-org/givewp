<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('wp_timezone_string')) {
    /**
     * Function is introduced in WP 5.3.
     *
     * @since 2.20.0
     *
     * @see https://developer.wordpress.org/reference/functions/wp_timezone_string
     */
    function wp_timezone_string() // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- WordPress core function, declared only when core lacks it (function_exists() guard above); the name must match core.
    {
        $timezone_string = get_option('timezone_string');

        if ($timezone_string) {
            return $timezone_string;
        }

        $offset = (float)get_option('gmt_offset');
        $hours = (int)$offset;
        $minutes = ($offset - $hours);

        $sign = ($offset < 0) ? '-' : '+';
        $abs_hour = abs($hours);
        $abs_mins = abs($minutes * 60);
        $tz_offset = sprintf('%s%02d:%02d', $sign, $abs_hour, $abs_mins);

        return $tz_offset;
    }
}

if (!function_exists('wp_timezone')) {
    /**
     * Function is introduced in WP 5.3.
     *
     * @since 2.20.0
     *
     * @see https://developer.wordpress.org/reference/functions/wp_timezone
     */
    function wp_timezone() // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- WordPress core function, declared only when core lacks it (function_exists() guard above); the name must match core.
    {
        return new DateTimeZone(wp_timezone_string());
    }
}

if (!function_exists('current_datetime')) {
    /**
     * Function is introduced in WP 5.3.
     *
     * @since 2.20.0
     *
     * @see https://developer.wordpress.org/reference/functions/current_datetime
     */
    function current_datetime() // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- WordPress core function, declared only when core lacks it (function_exists() guard above); the name must match core.
    {
        return date_create_immutable('now', wp_timezone());
    }
}
