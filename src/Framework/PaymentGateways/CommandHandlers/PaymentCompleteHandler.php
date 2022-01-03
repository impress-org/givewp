<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentComplete;

class PaymentCompleteHandler extends PaymentHandler
{
    /**
     * @param PaymentComplete $paymentCommand
     */
    public function __construct(PaymentComplete $paymentCommand)
    {
        parent::__construct($paymentCommand);
    }

    /**
     * @return string
     */
    protected function getPaymentStatus()
    {
        return 'complete';
    }
}
