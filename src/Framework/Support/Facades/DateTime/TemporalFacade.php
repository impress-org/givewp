<?php

namespace Give\Framework\Support\Facades\DateTime;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @since 2.19.6
 */
class TemporalFacade
{
    /**
     * @since 2.20.0 minor clean up and add types to signature
     * @since 2.19.6
     */
    public function toDateTime(string $date): DateTimeInterface
    {
        return DateTime::createFromFormat('Y-m-d H:i:s', $date, wp_timezone());
    }

    /**
     * @since 2.20.0 simplify and add types to signature
     * @since 2.19.6
     */
    public function getCurrentDateTime(): DateTimeInterface
    {
        return new DateTime('now', wp_timezone());
    }

    /**
     * @since 2.19.6
     *
     * @param DateTimeInterface $dateTime
     *
     * @return string
     */
    public function getFormattedDateTime(DateTimeInterface $dateTime)
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

    /**
     * Immutably returns a new DateTime instance with the microseconds set to 0.
     *
     * @unreleased Extracted new immutableOrClone method.
     * @since 2.20.0
     */
    public function withoutMicroseconds(DateTimeInterface $dateTime)
    {
        return $this
            ->immutableOrClone($dateTime)
            ->setTime(
                $dateTime->format('H'),
                $dateTime->format('i'),
                $dateTime->format('s')
            );
    }

    /**
     * Immutably returns a new DateTime instance with the time set to the start of the day.
     *
     * @unreleased
     */
    public function withStartOfDay(DateTimeInterface $dateTime): DateTimeInterface
    {
        return $this
            ->immutableOrClone($dateTime)
            ->setTime(0, 0, 0, 0);
    }

    /**
     * Immutably returns a new DateTime instance with the time set to the end of the day.
     *
     * @unreleased
     */
    public function withEndOfDay(DateTimeInterface $dateTime): DateTimeInterface
    {
        return $this
            ->immutableOrClone($dateTime)
            ->setTime(23, 59, 59, 999999);
    }

    /**
     * @unreleased
     */
    public function immutableOrClone(DateTimeInterface $dateTime): DateTimeInterface
    {
        return $dateTime instanceof DateTimeImmutable
            ? $dateTime
            : clone $dateTime;
    }
}
