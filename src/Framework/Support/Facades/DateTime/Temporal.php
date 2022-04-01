<?php

namespace Give\Framework\Support\Facades\DateTime;

use DateTime;
use Give\Framework\Support\Facades\Facade;

/**
 * @since 2.19.6
 *
 * @method static toDateTime(string $date): DateTime
 * @method static getCurrentDateTime(): DateTime
 * @method static getFormattedDateTime(DateTime $dateTime): string
 * @method static getCurrentFormattedDateForDatabase(): string
 */
class Temporal extends Facade
{
    protected function getFacadeAccessor()
    {
        return TemporalFacade::class;
    }
}
