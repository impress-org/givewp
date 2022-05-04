<?php

namespace Give\Framework\Support\Facades\DateTime;

use DateTime;

/**
 * @since 2.19.6
 */
class TemporalFacade
{
    /**
     * @unreleased minor clean up and add types to signature
     * @since 2.19.6
     */
    public function toDateTime(string $date): DateTime
    {
        return DateTime::createFromFormat('Y-m-d H:i:s', $date, wp_timezone());
    }

    /**
     * @unreleased simplify and add types to signature
     * @since 2.19.6
     */
    public function getCurrentDateTime(): DateTime
    {
        return new DateTime('now', wp_timezone());
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
