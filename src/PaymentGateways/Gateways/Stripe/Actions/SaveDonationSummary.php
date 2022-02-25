<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\DonationSummary;
use Give\PaymentGateways\Gateways\Stripe\WorkflowAction;

class SaveDonationSummary extends WorkflowAction
{
    public function __invoke( GatewayPaymentData $paymentData )
    {
        $summary = new DonationSummary( $paymentData );
        give_update_meta(
            $paymentData->donationId,
            '_give_stripe_donation_summary',
            $summary->getSummary()
        );
        $this->bind( $summary );
    }
}
