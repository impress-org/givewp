<?php

namespace Give\Donations\LegacyListeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;

class UpdateDonorPurchaseValueAndCount
{
    /**
     * @unreleased
     *
     * @param  Donation  $donation
     * @return void
     * @throws Exception
     */
    public function __invoke(Donation $donation)
    {
        /** @var Donor $donor */
        $donor = $donation->donor()->get();

        give()->donorRepository->updateLegacyColumns($donation->donorId, [
            'purchase_value' => $donor->totalAmountDonated(),
            'purchase_count' => $donor->totalDonations()
        ]);
    }
}
