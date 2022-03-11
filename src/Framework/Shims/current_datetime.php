<?php

/**
 * `current_datetime()` and `wp_timezone_string()` were added in WordPress 5.3.
 * GiveWP currently supports WordPress 5.0, so these functions need to be shimmed.
 */

if( ! function_exists( 'current_datetime' ) )
{
    /**
     * Retrieves the current time as an object using the site’s timezone.
     *
     * @return DateTimeImmutable|false
     */
    function current_datetime()
    {
        return date_create_immutable('now', wp_timezone());
    }
}

if( ! function_exists( 'wp_timezone' ) )
{
    /**
     * Retrieves the timezone of the site as a DateTimeZone object.
     *
     * @return DateTimeZone
     */
    function wp_timezone()
    {
        return new DateTimeZone(wp_timezone_string());
    }
}

if( ! function_exists( 'wp_timezone_string' ) )
{
    /**
     * Retrieves the timezone of the site as a string.
     *
     * @return mixed|string|void
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
