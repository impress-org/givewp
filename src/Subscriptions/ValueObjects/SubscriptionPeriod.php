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
     * @since 2.24.0
     *
     * @return array
     */
    public static function labels(): array
    {
        return [
            self::DAY => [__( 'Daily', 'give' ), __( 'Every %d days', 'give' )],
            self::WEEK => [__( 'Weekly', 'give' ), __( 'Every %d weeks', 'give' )],
            self::QUARTER => [__( 'Quarterly', 'give' ), __( 'Every %d quarters', 'give' )],
            self::MONTH => [__( 'Monthly', 'give' ), __( 'Every %d months', 'give' )],
            self::YEAR => [__( 'Yearly', 'give' ), __( 'Every %d years', 'give' )],
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
