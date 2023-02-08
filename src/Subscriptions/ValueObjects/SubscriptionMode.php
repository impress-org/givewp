<?php

namespace Give\Subscriptions\ValueObjects;

use MyCLabs\Enum\Enum;

/**
 * @since 2.19.6
 *
 * @method static SubscriptionMode TEST()
 * @method static SubscriptionMode LIVE()
 * @method bool isTest()
 * @method bool isLive()
 */
class SubscriptionMode extends Enum {
    const TEST = 'test';
    const LIVE = 'live';
}
