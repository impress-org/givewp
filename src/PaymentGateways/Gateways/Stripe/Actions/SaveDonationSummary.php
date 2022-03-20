<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\Framework\PaymentGateways\DonationSummary;

/**
 * @since 2.19.0
 */
class SaveDonationSummary
{
    /**
     * @param GatewayPaymentData $paymentData
     *
     * @return DonationSummary
     */
    public function __invoke(GatewayPaymentData $paymentData)
    {
        $summary = new DonationSummary($paymentData);
        give_update_meta(
            $paymentData->donationId,
            '_give_stripe_donation_summary',
            $summary->getSummaryWithDonor()
        );
        return $summary;
    }
}
