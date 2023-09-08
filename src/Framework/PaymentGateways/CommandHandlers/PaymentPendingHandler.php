<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\PaymentPending;

/**
 * @since 3.0.0
 */
class PaymentPendingHandler extends PaymentHandler
{
    /**
     * @since 3.0.0
     *
     * @param PaymentPending $paymentCommand
     */
    public function __construct(PaymentPending $paymentCommand)
    {
        parent::__construct($paymentCommand);
    }

    /**
     * @since 3.0.0
     *
     * @inheritDoc
     */
    protected function getPaymentStatus(): DonationStatus
    {
        return DonationStatus::PENDING();
    }
}
