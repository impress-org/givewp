<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Contracts;

use Stripe\Event;

/**
 * @since 2.21.0
 */
interface EventListener
{
    /**
     * @since 2.21.0
     *
     * @return void
     */
    public function processEvent(Event $event);
}
