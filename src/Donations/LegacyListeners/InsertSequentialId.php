<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;

class InsertSequentialId
{
    /**
     * @unreleased
     *
     * @param  Donation  $donation
     * @return void
     */
    public function __invoke(Donation $donation)
    {
        give()->seq_donation_number->__save_donation_title($donation->id, get_post($donation->id), false);
    }
}
