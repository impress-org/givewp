<?php

namespace Give\Revenue\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Revenue\Repositories\Revenue;

/**
 * @unreleased
 */
class UpdateRevenueWhenDonationAmountUpdated
{
    /**
     * @unreleased
     */
    public function __invoke(int $donationId)
    {
        $donation = Donation::find($donationId);

        if ($donation){
            give(Revenue::class)->updateRevenueAmount($donation);
        }
    }
}
