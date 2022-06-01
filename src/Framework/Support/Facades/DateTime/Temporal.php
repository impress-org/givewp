<?php

namespace Give\Framework\Support\Facades\DateTime;

use DateTime;
use DateTimeInterface;
use Give\Framework\Support\Facades\Facade;

/**
 * @since 2.19.6
 *
 * @method static toDateTime(string $date): DateTimeInterface
 * @method static getCurrentDateTime(): DateTimeInterface
 * @method static getFormattedDateTime(DateTime $dateTime): string
 * @method static getCurrentFormattedDateForDatabase(): string
 * @method static withoutMicroseconds(DateTimeInterface $dateTime): DateTimeInterface
 */
class Temporal extends Facade
{
    protected function getFacadeAccessor()
    {
        return TemporalFacade::class;
    }
}
