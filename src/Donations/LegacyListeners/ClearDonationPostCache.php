<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;

/**
 * @since 2.25.0
 */
class ClearDonationPostCache
{
    /**
     * @since 2.25.0
     */
    public function __invoke(Donation $donation)
    {
        clean_post_cache($donation->id);
    }
}
