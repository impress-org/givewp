<?php

namespace Give\Framework\ListTable\Formatting;

use DateTimeInterface;
use Give\Framework\ListTable\Views\Placeholder;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * The one way list tables write dates. It uses the date and time formats and the timezone of the site.
 *
 * @since TBD
 */
final class DateFormatter
{
    /**
     * @since TBD
     */
    public static function dateTime(?DateTimeInterface $date): string
    {
        if (self::isMissing($date)) {
            return Placeholder::dash(__('No date', 'give'));
        }

        return Temporal::getFormattedDateTimeUsingTimeZoneAndFormatSettings($date);
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
