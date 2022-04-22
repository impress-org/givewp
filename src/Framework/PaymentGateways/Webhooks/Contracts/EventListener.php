<?php

namespace Give\Framework\PaymentGateways\Webhooks\Contracts;

/**
 * @unreleased
 */
interface EventListener
{
    /**
     * @unreleased
     *
     * @param mixed $event
     *
     * @return void
     */
    public function processEvent($event);
}
