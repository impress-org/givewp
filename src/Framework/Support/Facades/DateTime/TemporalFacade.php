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
     * @unreleased for consistency, match the DateTime to be the same as $this->toDateTime
     * @since 2.19.6
     *
     * @return DateTime
     */
    public function getCurrentDateTime()
    {
        $now = date_create('now', wp_timezone())->format('Y-m-d H:i:s');

        return $this->toDateTime($now);
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
