<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentAbandoned;

class PaymentAbandonedHandler extends PaymentHandler
{
    /**
     * @param PaymentAbandoned $paymentCommand
     */
    public function __construct(PaymentAbandoned $paymentCommand)
    {
        parent::__construct($paymentCommand);
    }

    /**
     * @return string
     */
    protected function getPaymentStatus()
    {
        return 'abandoned';
    }
}
