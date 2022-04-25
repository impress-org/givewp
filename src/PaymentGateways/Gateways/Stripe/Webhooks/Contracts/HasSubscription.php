<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Contracts;

use Give\Subscriptions\Models\Subscription;
use Stripe\Event;

/**
 * @unreleased
 */
interface HasSubscription
{
    /**
     * @unreleased
     * @return Subscription
     */
    public function getSubscription(Event $event);
}
