<?php

namespace Give\Revenue\Listeners;

use Give\Donations\Models\Donation;
use Give\Revenue\Repositories\Revenue;

/**
 * @unreleased
 */
class UpdateRevenueWhenDonationAmountUpdated
{
    /**
     * @unreleased
     *
     * @param int $paymentID The ID of the payment.
     */
    public function __invoke($paymentID)
    {
        give(Revenue::class)->updateRevenueAmount(
            Donation::find($paymentID)
        );
    }
}
