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
 */
class Temporal extends Facade
{
    const TIMESTAMP = 'Y-m-d H:i:s';

    protected function getFacadeAccessor(): string
    {
        return TemporalFacade::class;
    }
}
