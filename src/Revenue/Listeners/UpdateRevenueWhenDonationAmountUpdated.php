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
     * @param int $donationId The ID of the Donation.
     */
    public function __invoke($donationId)
    {
        give(Revenue::class)->updateRevenueAmount(
            Donation::find($donationId)
        );
    }
}
