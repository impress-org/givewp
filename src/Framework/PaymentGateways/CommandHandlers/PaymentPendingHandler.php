<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\PaymentPending;

class PaymentPendingHandler extends PaymentHandler
{
    /**
     * @param PaymentPending $paymentCommand
     */
    public function __construct(PaymentPending $paymentCommand)
    {
        parent::__construct($paymentCommand);
    }

    protected function getPaymentStatus(): DonationStatus
    {
        return DonationStatus::PENDING();
    }
}
