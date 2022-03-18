<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Donations\ValueObjects\DonationStatus;
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
     * @return DonationStatus
     */
    protected function getPaymentStatus()
    {
        return DonationStatus::COMPLETE();
    }
}
