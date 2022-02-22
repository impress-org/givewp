<?php

namespace Give\Donations\Listeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Helpers\Hooks;

class DonationUpdated {
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

            $this->dispatchDonationStatusUpdated(
                $donation->id,
                $donation->status->getValue(),
                $originalStatus->getValue()
            );
        }
    }

    /**
     * Fires after changing donation status.
     *
     * @unreleased
     *
     * @param  int  $donationId
     * @param  string  $newStatus
     * @param  string  $originalStatus
     * @return void
     */
    private function dispatchDonationStatusUpdated($donationId, $newStatus, $originalStatus)
    {
        /**
         * @unreleased
         */
        Hooks::dispatch('give_donation_status_updated', $donationId, $newStatus, $originalStatus);

        /**
         * @deprecated
         */
        Hooks::dispatch('give_update_payment_status', $donationId, $newStatus, $originalStatus);
    }
}
