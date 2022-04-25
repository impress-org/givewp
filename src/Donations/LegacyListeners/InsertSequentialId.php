<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;

class InsertSequentialId
{
    /**
     * @since 2.19.6
     *
     * @param  Donation  $donation
     * @return void
     */
    public function __invoke(Donation $donation)
    {
        give()->seq_donation_number->__save_donation_title($donation->id, get_post($donation->id), false);
    }
}
