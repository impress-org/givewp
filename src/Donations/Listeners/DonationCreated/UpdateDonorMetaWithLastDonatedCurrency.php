<?php

namespace Give\Donations\Listeners\DonationCreated;

use Give\Donations\Models\Donation;

/**
 * Ported over from v2 forms `give_cs_store_switched_currency_meta_data`
 *
 * @unreleased
 */
class UpdateDonorMetaWithLastDonatedCurrency
{
    /**
     * @unreleased
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
