<?php

namespace unit\tests\Subscriptions\Traits;

use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;

trait SubscriptionFactory {
     /**
     * @unreleased
     *
     * @return Subscription
     */
    private function createSubscriptionInstance()
    {
        return new Subscription(50, SubscriptionPeriod::MONTH(), 1, 1);
    }
}
