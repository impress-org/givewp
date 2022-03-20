<?php

namespace Give\Framework\Support\Facades\DateTime;

use DateTime;

/**
 * @unreleased
 */
class TemporalFacade
{
    /**
     * @unreleased
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
     * @unreleased
     *
     * @return DateTime
     */
    public function getCurrentDateTime()
    {
        return date_create('now', wp_timezone());
    }

    /**
     * @unreleased
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
     * @unreleased
     *
     * @return string
     */
    public function getCurrentFormattedDateForDatabase()
    {
        return current_datetime()->format('Y-m-d H:i:s');
    }
}
