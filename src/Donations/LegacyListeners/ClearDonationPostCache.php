<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;

/**
 * @unreleased
 */
class ClearDonationPostCache
{
    /**
     * @unreleased
     */
    public function __invoke(Donation $donation)
    {
        clean_post_cache($donation->id);
    }
}
