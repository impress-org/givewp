<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentRefunded;

class PaymentRefundedHandler extends PaymentHandler
{
    /**
     * @param PaymentRefunded $paymentCommand
     */
    public function __construct(PaymentRefunded $paymentCommand)
    {
        parent::__construct($paymentCommand);
    }

    /**
     * @return string
     */
    protected function getPaymentStatus()
    {
        return 'refunded';
    }
}
