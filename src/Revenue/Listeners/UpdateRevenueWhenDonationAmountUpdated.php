<?php

namespace Give\Revenue\Listeners;

use Give\Donations\Models\Donation;
use Give\Revenue\Repositories\Revenue;

/**
 * @since 2.22.1
 */
class UpdateRevenueWhenDonationAmountUpdated
{
    /**
     * @since 2.22.1
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
