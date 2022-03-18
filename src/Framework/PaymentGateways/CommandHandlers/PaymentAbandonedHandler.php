<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Donations\ValueObjects\DonationStatus;
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
     * @return DonationStatus
     */
    protected function getPaymentStatus()
    {
        return DonationStatus::ABANDONED();
    }
}
