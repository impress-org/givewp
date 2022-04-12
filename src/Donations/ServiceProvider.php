<?php

namespace Give\Donations;

use Give\Donations\LegacyListeners\DispatchGiveInsertPayment;
use Give\Donations\LegacyListeners\DispatchGiveRecurringAddSubscriptionPaymentAndRecordPayment;
use Give\Donations\LegacyListeners\DispatchGiveUpdatePaymentStatus;
use Give\Donations\LegacyListeners\InsertSequentialId;
use Give\Donations\LegacyListeners\RemoveSequentialId;
use Give\Donations\LegacyListeners\UpdateDonorPaymentIds;
use Give\Donations\LegacyListeners\UpdateSequentialId;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use Give\Helpers\Call;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('donations', DonationRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $this->bootLegacyListeners();
    }

    /**
     * Legacy Listeners
     *
     * @since 2.19.6
     */
    private function bootLegacyListeners()
    {
        add_action('give_donation_created', function (Donation $donation) {
            Call::invoke(InsertSequentialId::class, $donation);
            Call::invoke(DispatchGiveInsertPayment::class, $donation);
            Call::invoke(UpdateDonorPaymentIds::class, $donation);

            if ($donation->subscriptionId) {
                Call::invoke(DispatchGiveRecurringAddSubscriptionPaymentAndRecordPayment::class, $donation);
            }

            /**
             * @notice
             * Anytime we call give_update_payment_status the donor purchase_value and purchase_count get affected.
             * We are doing this in the gateway api and in many other places.
             * The listener below matches the functionality but the count seems to be overwritten elsewhere.
             * Leaving this commented out until resolved or needed.
             */
            //Call::invoke(UpdateDonorPurchaseValueAndCount::class, $donation);
        });

        add_action('give_donation_updated', function (Donation $donation) {
            Call::invoke(DispatchGiveUpdatePaymentStatus::class, $donation);
            Call::invoke(UpdateSequentialId::class, $donation);
        });

        Hooks::addAction('give_donation_deleted', RemoveSequentialId::class);
    }
}
