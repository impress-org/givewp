<?php

namespace Give\Donations;

use Give\Donations\LegacyListeners\DispatchGiveInsertPayment;
use Give\Donations\LegacyListeners\DispatchGivePreInsertPayment;
use Give\Donations\LegacyListeners\DispatchGiveRecurringAddSubscriptionPaymentAndRecordPayment;
use Give\Donations\LegacyListeners\DispatchGiveUpdatePaymentStatus;
use Give\Donations\LegacyListeners\InsertSequentialId;
use Give\Donations\LegacyListeners\RemoveSequentialId;
use Give\Donations\LegacyListeners\UpdateDonorPaymentIds;
use Give\Donations\LegacyListeners\UpdateSequentialId;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationNotesRepository;
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
        give()->singleton('donationNotes', DonationNotesRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $this->bootLegacyListeners();
        $this->registerDonationsAdminPage();
    }

    /**
     * Legacy Listeners
     *
     * @since 2.19.6
     */
    private function bootLegacyListeners()
    {
        Hooks::addAction('givewp_donation_creating', DispatchGivePreInsertPayment::class);

        add_action('givewp_donation_created', function (Donation $donation) {
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

        add_action('givewp_donation_updated', function (Donation $donation) {
            Call::invoke(DispatchGiveUpdatePaymentStatus::class, $donation);
            Call::invoke(UpdateSequentialId::class, $donation);
        });

        Hooks::addAction('givewp_donation_deleted', RemoveSequentialId::class);
    }

    /**
     * Donations Admin page
     *
     * @since 2.20.0
     */
    private function registerDonationsAdminPage()
    {
        $userId = get_current_user_id();
        $showLegacy = get_user_meta($userId, '_give_donations_archive_show_legacy', true);
        // only register new admin page if user hasn't chosen to use the old one
        if(empty($showLegacy))
        {
            Hooks::addAction('admin_menu', DonationsAdminPage::class, 'registerMenuItem');

            if (DonationsAdminPage::isShowing()) {
                Hooks::addAction('admin_enqueue_scripts', DonationsAdminPage::class, 'loadScripts');
            }
        }
    }
}
