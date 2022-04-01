<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Helpers\Hooks;

class DispatchGiveUpdatePaymentStatus
{
    /**
     * Dispatching this action also ensures additional actions are triggered in legacy files.
     * Increasing Donor purchase_value and purchase_count is included.
     * @see * includes/payments/actions.php
     * add_action( 'give_update_payment_status', 'give_complete_purchase', 100, 3 );
     * add_action( 'give_update_payment_status', 'give_record_status_change', 100, 3 );
     *
     * @since 2.19.6
     *
     * @param  Donation  $donation
     * @return void
     */
    public function __invoke(Donation $donation)
    {
        if ($donation->isDirty('status')) {
            /** @var DonationStatus $originalStatus */
            $originalStatus = $donation->getOriginal('status');

            Hooks::doAction(
                'give_update_payment_status',
                $donation->id,
                $donation->status->getValue(),
                $originalStatus->getValue()
            );
        }
    }
}
