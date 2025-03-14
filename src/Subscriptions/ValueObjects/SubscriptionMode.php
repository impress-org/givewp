<?php

namespace Give\Subscriptions\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

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
