<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners;

interface EventListener
{
    /**
     * This processes the PayPal Commerce webhook event passed to it.
     *
     * @since 2.9.0
     *
     * @param object $event
     *
     * @return void
     */
    public function processEvent($event);
}
