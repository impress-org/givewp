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
     * @since 3.3.0 updated to accept Donation model
     * @since 2.22.1
     */
    public function __invoke(Donation $donation)
    {
        if ($donation->isDirty('amount')) {
            give(Revenue::class)->updateRevenueAmount($donation);
        }
    }
}
