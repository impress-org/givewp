<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Donations\ValueObjects\DonationStatus;
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
     * @return DonationStatus
     */
    protected function getPaymentStatus(): DonationStatus
    {
        return DonationStatus::PROCESSING();
    }
}
