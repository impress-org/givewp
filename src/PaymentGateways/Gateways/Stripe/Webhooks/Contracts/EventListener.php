<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Contracts;

use Stripe\Event;

/**
 * @unreleased
 */
interface EventListener
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function processEvent(Event $event);
}
