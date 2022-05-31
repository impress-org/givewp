<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Donations\ValueObjects\DonationStatus;
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
     * @inheritdoc
     */
    protected function getPaymentStatus(): DonationStatus
    {
        return DonationStatus::REFUNDED();
    }
}
