<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentProcessing;

class PaymentProcessingHandler extends PaymentHandler
{
    /**
     * @param PaymentProcessing $paymentCommand
     */
    public function __construct(PaymentProcessing $paymentCommand)
    {
        parent::__construct($paymentCommand);
    }

    /**
     * @return string
     */
    protected function getPaymentStatus()
    {
        return 'processing';
    }
}
