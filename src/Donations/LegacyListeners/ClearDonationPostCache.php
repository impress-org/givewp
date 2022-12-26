<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;

class ClearDonationPostCache
{
    public function __invoke(Donation $donation)
    {
        clean_post_cache($donation->id);
    }
}
