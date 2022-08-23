<?php

namespace Give\Revenue\Listeners;

use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;
use Give\Revenue\Repositories\Revenue;

class UpdateRevenueWhenDonationAmountUpdated
{
    /**
     * @param int $paymentID The ID of the payment.
     */
    public function __invoke($paymentID)
    {
        give(Revenue::class)->updateRevenueAmount(
            Donation::find($paymentID)
        );
    }
}
