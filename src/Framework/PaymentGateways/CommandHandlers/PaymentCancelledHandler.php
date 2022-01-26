<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

/**
 * @unreleased
 */
class PaymentCancelledHandler extends PaymentHandler
{

    /**
     * @inheritDoc
     */
    protected function getPaymentStatus()
    {
        return 'cancelled';
    }
}
