<?php

namespace Give\Framework\Support\Facades\DateTime;

use DateTimeInterface;
use Give\Framework\Support\Facades\Facade;

/**
 * @unreleased added withStartOfDay, withEndOfDay, immutableOrClone
 * @since 3.20. added getDateTimestamp
 * @since 2.19.6
 *
 * @method static DateTimeInterface toDateTime(string $date)
 * @method static DateTimeInterface getCurrentDateTime()
 * @method static string getFormattedDateTime(DateTimeInterface $dateTime)
 * @method static string getCurrentFormattedDateForDatabase()
 * @method static DateTimeInterface withoutMicroseconds(DateTimeInterface $dateTime)
 * @method static DateTimeInterface withStartOfDay(DateTimeInterface $dateTime)
 * @method static DateTimeInterface withEndOfDay(DateTimeInterface $dateTime)
 * @method static DateTimeInterface immutableOrClone(DateTimeInterface $dateTime)
 * @method static int getDateTimestamp(string $date, string $timezone = '')
 */
class Temporal extends Facade
{
    protected function getFacadeAccessor(): string
    {
        return TemporalFacade::class;
    }
}
