<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

/**
 * @unreleased
 */
class PaymentFailedHandler extends PaymentHandler
{

    /**
     * @inheritDoc
     */
    protected function getPaymentStatus()
    {
        return 'failed';
    }
}
