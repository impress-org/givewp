<?php

declare(strict_types=1);

if (!function_exists('wp_timezone_string')) {
    /**
     * Function is introduced in WP 5.3.
     *
     * @since 2.20.0
     *
     * @see https://developer.wordpress.org/reference/functions/wp_timezone_string
     */
    function wp_timezone_string()
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
    function wp_timezone()
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
    function current_datetime()
    {
        return date_create_immutable('now', wp_timezone());
    }
}
