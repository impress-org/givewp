<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Helpers\Hooks;

class DispatchGiveUpdatePaymentStatus
{
    /**
     * @unreleased
     *
     * @param  Donation  $donation
     * @return void
     */
    public function __invoke(Donation $donation)
    {
        if ($donation->isDirty('status')) {
            /** @var DonationStatus $originalStatus */
            $originalStatus = $donation->getOriginal('status');

            Hooks::dispatch(
                'give_update_payment_status',
                $donation->id,
                $donation->status->getValue(),
                $originalStatus->getValue()
            );
        }
    }
}
