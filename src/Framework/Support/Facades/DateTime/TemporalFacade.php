<?php

namespace Give\Framework\Support\Facades\DateTime;

use DateTime;

/**
 * @since 2.19.6
 */
class TemporalFacade
{
    /**
     * @since 2.19.6
     *
     * @param  string  $date
     *
     * @return DateTime
     */
    public function toDateTime($date)
    {
        $timezone = wp_timezone();

        return date_create_from_format('Y-m-d H:i:s', $date, $timezone)->setTimezone($timezone);
    }

    /**
     * @since 2.19.6
     *
     * @return DateTime
     */
    public function getCurrentDateTime()
    {
        return date_create('now', wp_timezone());
    }

    /**
     * @since 2.19.6
     *
     * @param  DateTime  $dateTime
     *
     * @return string
     */
    public function getFormattedDateTime(DateTime $dateTime)
    {
        return $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * @since 2.19.6
     *
     * @return string
     */
    public function getCurrentFormattedDateForDatabase()
    {
        return current_datetime()->format('Y-m-d H:i:s');
    }
}
