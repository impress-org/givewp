<?php

namespace Give\Framework\Models\Traits;

use DateTime;
use Exception;

/**
 * @unreleased
 */
trait InteractsWithTime
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
     *
     * @throws Exception
     */
    public function getCurrentDateTime()
    {
        return (new DateTime('now', wp_timezone()));
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
        return $dateTime->format( 'Y-m-d H:i:s' );
    }
}
