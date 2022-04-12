<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;

class RemoveSequentialId
{
    /**
     * @since 2.19.6
     *
     * @param  Donation  $donation
     * @return void
     */
    public function __invoke(Donation $donation)
    {
        give()->seq_donation_number->__remove_serial_number($donation->id);
    }
}
