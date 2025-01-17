<?php

namespace Give\Framework\Support\Facades\DateTime;

use DateTimeInterface;
use Give\Framework\Support\Facades\Facade;

/**
 * @since 2.19.6
 *
 * @method static DateTimeInterface toDateTime(string $date)
 * @method static DateTimeInterface getCurrentDateTime()
 * @method static string getFormattedDateTime(DateTimeInterface $dateTime)
 * @method static string getCurrentFormattedDateForDatabase()
 * @method static DateTimeInterface withoutMicroseconds(DateTimeInterface $dateTime)
 * @method static int getDateTimestamp(string $date, string $timezone = '')
 */
class Temporal extends Facade
{
    protected function getFacadeAccessor(): string
    {
        return TemporalFacade::class;
    }
}
