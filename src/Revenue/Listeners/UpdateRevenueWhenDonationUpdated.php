<?php

namespace Give\Revenue\Listeners;

use Give\Donations\Models\Donation;
use Give\Revenue\Repositories\Revenue;

/**
 * @since 4.1.0 Updated class to support update other properties
 * @since 2.22.1
 */
class UpdateRevenueWhenDonationUpdated
{
    /**
     * @since 4.1.0 Added support to update revenue campaignId
     * @since 3.3.0 updated to accept Donation model
     * @since 2.22.1
     */
    public function __invoke(Donation $donation)
    {
        if ($donation->isDirty('amount')) {
            give(Revenue::class)->updateRevenueAmount($donation);
        }

        if ($donation->isDirty('campaignId')) {
            give(Revenue::class)->updateRevenueCampaignId($donation);
        }
    }
}
