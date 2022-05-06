<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\DonationSummary;

/**
 * @since 2.19.0
 */
class SaveDonationSummary
{
    /**
     * @since 2.19.0
     */
    public function __invoke(Donation $donation): DonationSummary
    {
        $summary = new DonationSummary($donation);
        give_update_meta(
            $donation->id,
            '_give_stripe_donation_summary',
            $summary->getSummaryWithDonor()
        );

        return $summary;
    }
}
