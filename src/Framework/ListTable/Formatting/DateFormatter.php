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
     * A missing date, the zero date ("0000-00-00") and the exact epoch (what an empty value turns into) count
     * as no date. A real date before 1970, such as an imported or backdated donation, is still written out.
     *
     * @since TBD
     */
    private static function isMissing(?DateTimeInterface $date): bool
    {
        if ($date === null || $date->getTimestamp() === 0) {
            return true;
        }

        // "0000-00-00" becomes the year -1 in whatever timezone the date was built in, so the year tells the
        // zero date from a real one without depending on the timezone.
        return (int) $date->format('Y') < 1;
    }

    /**
     * The unformatted date for a row's "rawValues" key: an ISO 8601 string in UTC, or null when there is no date.
     *
     * @since TBD
     */
    public static function iso(?DateTimeInterface $date): ?string
    {
        if (self::isMissing($date)) {
            return null;
        }

        return gmdate('Y-m-d\TH:i:s\Z', $date->getTimestamp());
    }
}
