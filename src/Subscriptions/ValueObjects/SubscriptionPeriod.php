<?php

namespace Give\Subscriptions\ValueObjects;

use MyCLabs\Enum\Enum;

/**
 * @unreleased
 *
 * @method static DAY()
 * @method static WEEK()
 * @method static MONTH()
 * @method static QUARTER()
 * @method static YEAR()
 */
class SubscriptionPeriod extends Enum {
    const DAY = 'day';
    const WEEK = 'week';
    const QUARTER = 'quarter';
    const MONTH = 'month';
    const YEAR = 'year';
}
