<?php

namespace Give\Subscriptions\ValueObjects;


use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 2.19.6
 *
 * @method static SubscriptionPeriod DAY()
 * @method static SubscriptionPeriod WEEK()
 * @method static SubscriptionPeriod MONTH()
 * @method static SubscriptionPeriod QUARTER()
 * @method static SubscriptionPeriod YEAR()
 * @method bool isDay
 * @method bool isWeek
 * @method bool isMonth
 * @method bool isQuarter
 * @method bool isYear
 */
class SubscriptionPeriod extends Enum {
    const DAY = 'day';
    const WEEK = 'week';
    const QUARTER = 'quarter';
    const MONTH = 'month';
    const YEAR = 'year';

    /**
     * @since TBD Add translators comments.
     * @since 2.24.0
     *
     * @return array
     */
    public static function labels(): array
    {
        return [
            self::DAY => [__( 'Daily', 'give' ), /* translators: %d: Number of days */ __( 'Every %d days', 'give' )],
            self::WEEK => [__( 'Weekly', 'give' ), /* translators: %d: Number of weeks */ __( 'Every %d weeks', 'give' )],
            self::QUARTER => [__( 'Quarterly', 'give' ), /* translators: %d: Number of quarters */ __( 'Every %d quarters', 'give' )],
            self::MONTH => [__( 'Monthly', 'give' ), /* translators: %d: Number of months */ __( 'Every %d months', 'give' )],
            self::YEAR => [__( 'Yearly', 'give' ), /* translators: %d: Number of years */ __( 'Every %d years', 'give' )],
        ];
    }

    /**
     * @since 2.24.0
     *
     * @param int $frequency
     *
     * @return string
     */
    public function label(int $frequency): string
    {
        return self::labels()[ $this->getValue() ][$frequency > 1];
    }
}
