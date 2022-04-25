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
}
