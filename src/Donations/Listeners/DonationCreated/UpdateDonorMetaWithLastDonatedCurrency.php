<?php

namespace Give\Donations\Listeners\DonationCreated;

use Give\Donations\Models\Donation;

/**
 * Ported over from v2 forms `give_cs_store_switched_currency_meta_data`
 *
 * @since 4.2.0
 */
class UpdateDonorMetaWithLastDonatedCurrency
{
    /**
     * @since 4.2.0
     */
    public function __invoke(Donation $donation)
    {
        $donorCurrency = give()->donor_meta->get_meta($donation->donorId, '_give_cs_currency', true);

        if ($donorCurrency !== $donation->amount->getCurrency()->getCode()) {
            give()->donor_meta->update_meta(
                $donation->donorId,
                '_give_cs_currency',
                $donation->amount->getCurrency()->getCode()
            );
        }
    }
}
