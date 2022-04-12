<?php

namespace Give\Donations\LegacyListeners;

use Exception;
use Give\Donations\Models\Donation;

class UpdateDonorPurchaseValueAndCount
{
    /**
     * @since 2.19.6
     *
     * @param  Donation  $donation
     * @return void
     * @throws Exception
     */
    public function __invoke(Donation $donation)
    {
        $donor = $donation->donor;

        give()->donors->updateLegacyColumns($donation->donorId, [
            'purchase_value' => $donor->totalAmountDonated(),
            'purchase_count' => $donor->totalDonations()
        ]);
    }
}
