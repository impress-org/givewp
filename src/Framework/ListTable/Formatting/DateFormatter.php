<?php

namespace Give\Framework\ListTable\Formatting;

use DateTimeInterface;
use Give\Framework\ListTable\Views\Placeholder;

/**
 * The one way list tables write dates. It uses the date and time formats and the timezone of the site.
 *
 * @since TBD
 */
final class DateFormatter
{
    /**
     * Writes the date and the time with a translatable word between them, so the text is in one
     * language on every site. In English it reads "October 29, 2026 at 10:35 pm".
     *
     * @since TBD
     */
    public static function dateTime(?DateTimeInterface $date): string
    {
        if (self::isMissing($date)) {
            return Placeholder::dash(__('No date', 'give'));
        }

        $timestamp = $date->getTimestamp();
        $timezone = wp_timezone();

        return sprintf(
            /* translators: 1: a formatted date, 2: a formatted time. */
            _x('%1$s at %2$s', 'date and time', 'give'),
            wp_date(get_option('date_format'), $timestamp, $timezone),
            wp_date(get_option('time_format'), $timestamp, $timezone)
        );
    }

    /**
     * @since TBD
     */
    public static function date(?DateTimeInterface $date): string
    {
        if (self::isMissing($date)) {
            return Placeholder::dash(__('No date', 'give'));
        }

        return wp_date(get_option('date_format'), $date->getTimestamp(), wp_timezone());
    }

    /**
     * @since TBD
     */
    private static function isMissing(?DateTimeInterface $date): bool
    {
        return $date === null || $date->getTimestamp() <= 0;
    }
}
