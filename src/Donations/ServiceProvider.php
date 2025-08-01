<?php

namespace Give\Donations;

use Give\Donations\Actions\LoadDonationAdminOptions;
use Give\Donations\Actions\RegisterDonationEntity;
use Give\Donations\CustomFields\Controllers\DonationDetailsController;
use Give\Donations\LegacyListeners\ClearDonationPostCache;
use Give\Donations\LegacyListeners\DispatchDonationNoteEmailNotification;
use Give\Donations\LegacyListeners\DispatchGiveInsertPayment;
use Give\Donations\LegacyListeners\DispatchGivePreInsertPayment;
use Give\Donations\LegacyListeners\DispatchGiveRecurringAddSubscriptionPaymentAndRecordPayment;
use Give\Donations\LegacyListeners\DispatchGiveUpdatePaymentStatus;
use Give\Donations\LegacyListeners\InsertSequentialId;
use Give\Donations\LegacyListeners\RemoveSequentialId;
use Give\Donations\LegacyListeners\UpdateDonorPaymentIds;
use Give\Donations\LegacyListeners\UpdateDonationMetaWithLegacyFormCurrencySettings;
use Give\Donations\Listeners\DonationCreated\UpdateDonationMetaWithCurrencySettings;
use Give\Donations\Listeners\DonationCreated\UpdateDonorMetaWithLastDonatedCurrency;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\Migrations\AddMissingDonorIdToDonationComments;
use Give\Donations\Migrations\MoveDonationCommentToDonationMetaTable;
use Give\Donations\Migrations\RecalculateExchangeRate;
use Give\Donations\Migrations\SetAutomaticFormattingOption;
use Give\Donations\Migrations\UnserializeTitlePrefix;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationNotesRepository;
use Give\Donations\Repositories\DonationRepository;
use Give\Framework\Migrations\MigrationsRegister;
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
        give()->singleton(DonationsListTable::class, function () {
            $listTable = new DonationsListTable();
            Hooks::doAction('givewp_donations_list_table', $listTable);

            return $listTable;
        });
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $this->bootListeners();
        $this->bootLegacyListeners();
        $this->registerDonationsAdminPage();
        $this->addCustomFieldsToDonationDetails();
        $this->registerDonationEntity();
        $this->loadDonationAdminOptions();

        give(MigrationsRegister::class)->addMigrations([
            AddMissingDonorIdToDonationComments::class,
            SetAutomaticFormattingOption::class,
            MoveDonationCommentToDonationMetaTable::class,
            UnserializeTitlePrefix::class,
            RecalculateExchangeRate::class,
        ]);
    }

    /**
     * Legacy Listeners
     * @since 2.25.0 Call ClearDonationPostCache on the "givewp_donation_updated" hook
     * @since 2.24.0 Remove UpdateSequentialId from "givewp_donation_updated" action hook.
     * @since 2.19.6
     */
    private function bootLegacyListeners()
    {
        Hooks::addAction('givewp_donation_creating', DispatchGivePreInsertPayment::class);

        add_action('givewp_donation_created', static function (Donation $donation) {
            (new UpdateDonationMetaWithLegacyFormCurrencySettings())($donation);
            (new InsertSequentialId())($donation);
            (new DispatchGiveInsertPayment())($donation);
            (new UpdateDonorPaymentIds())($donation);

            if ($donation->subscriptionId && $donation->type->isRenewal()) {
                (new DispatchGiveRecurringAddSubscriptionPaymentAndRecordPayment())($donation);
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
            (new ClearDonationPostCache())($donation);
            (new DispatchGiveUpdatePaymentStatus())($donation);
        });

        Hooks::addAction('givewp_donation_deleted', RemoveSequentialId::class);

        add_action('givewp_donation_note_created', static function ($donationNote) {
            if ($donationNote->type->isDonor()) {
                (new DispatchDonationNoteEmailNotification())($donationNote);
            }
        });
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
        if (empty($showLegacy)) {
            Hooks::addAction('admin_menu', DonationsAdminPage::class, 'registerMenuItem', 20);
        }
    }

    /**
     * @since 3.0.0
     */
    private function addCustomFieldsToDonationDetails()
    {
        add_action('give_view_donation_details_billing_after', static function ($donationId) {
            echo (new DonationDetailsController())->show($donationId);
        });
    }

    /**
     * @since 4.2.0
     */
    private function bootListeners()
    {
        add_action('givewp_donation_created', static function (Donation $donation) {
            (new UpdateDonationMetaWithCurrencySettings())($donation);

            (new UpdateDonorMetaWithLastDonatedCurrency())($donation);
        });
    }

    /**
     * @since 4.6.0
     */
    private function registerDonationEntity()
    {
        Hooks::addAction('init', RegisterDonationEntity::class);
    }

    /**
     * @since 4.6.1 Move to admin_enqueue_scripts hook
     * @since 4.6.0
     */
    private function loadDonationAdminOptions()
    {
        add_action('admin_enqueue_scripts', function () {
            if (DonationsAdminPage::isShowingDetailsPage()) {
                give(LoadDonationAdminOptions::class)();
            }
        });
    }
}
