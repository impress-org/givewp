<?php

namespace Give\PaymentGateways\PayPalStandard\Gateways\Webhooks\Listeners;

interface EventListener
{
    /**
     * This processes the PayPal Standard webhook event passed to it.
     *
     * @unreleased
     *
     * @param object $eventData Paypal ipn data.
     *
     * @return void
     */
    public function processEvent($eventData);
}
