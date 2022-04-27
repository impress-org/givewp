<?php

namespace Give\Donations\LegacyListeners;

use Exception;
use Give\Donations\Models\Donation;

class UpdateDonorPaymentIds
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
        $ids = give()->donations->getAllDonationIdsByDonorId($donation->donorId);

        $paymentIds = implode( ',', array_unique( array_values(  $ids ) ) );

        give()->donors->updateLegacyColumns($donation->donorId, ['payment_ids' => $paymentIds]);
    }
}
