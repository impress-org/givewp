<?php

namespace Give\Revenue\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Revenue\Repositories\Revenue;

/**
 * @since 3.3.0
 */
class UpdateRevenueWhenDonationAmountUpdated
{
    /**
     * @since 3.3.0
     */
    public function __invoke(int $donationId)
    {
        $donation = Donation::find($donationId);

        if ($donation){
            give(Revenue::class)->updateRevenueAmount($donation);
        }
    }
}
