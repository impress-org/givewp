<?php

namespace Give\Subscriptions;

use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Subscriptions\Actions\LoadSubscriptionAdminOptions;
use Give\Subscriptions\LegacyListeners\DispatchGiveSubscriptionPostCreate;
use Give\Subscriptions\LegacyListeners\DispatchGiveSubscriptionPreCreate;
use Give\Subscriptions\ListTable\SubscriptionsListTable;
use Give\Subscriptions\Migrations\AddCampaignId;
use Give\Subscriptions\Migrations\AddCampaignIdColumn;
use Give\Subscriptions\Migrations\AddPaymentModeToSubscriptionTable;
use Give\Subscriptions\Migrations\BackfillMissingCampaignIdForDonations;
use Give\Subscriptions\Migrations\CreateSubscriptionTables;
use Give\Subscriptions\Migrations\UpdateProductID;
use Give\Subscriptions\Repositories\SubscriptionNotesRepository;
use Give\Subscriptions\Repositories\SubscriptionRepository;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     *
     * @since 4.8.0 Register Subscription Repository to container
     */
    public function register()
    {
        give()->singleton('subscriptions', SubscriptionRepository::class);
        give()->singleton('subscriptionNotes', SubscriptionNotesRepository::class);
        give()->singleton(SubscriptionsListTable::class, function() {
            $listTable = new SubscriptionsListTable();
            Hooks::doAction('givewp_subscriptions_list_table', $listTable);

            return $listTable;
        });
    }

    /**
     * @inheritDoc
     *
     * @since 4.14.0 move subscription page registration to method to defer conditionals and DB queries to appropriate hooks.
     */
    public function boot()
    {
        $this->bootLegacyListeners();
        $this->registerMigrations();
        $this->registerSubscriptionAdminOptions();
        $this->registerSubscriptionsAdminPage();
    }

    /**
     * Register the subscriptions admin page, deferring conditionals and DB queries to appropriate hooks.
     *
     * @since 4.14.0
     */
    private function registerSubscriptionsAdminPage()
    {
        // Register new admin page if user hasn't chosen to use the legacy one
        add_action('give_forms_page_give-subscriptions', function () {
            if ($this->shouldShowLegacySubscriptionsPage()) {
                return;
            }

            give(SubscriptionsAdminPage::class)->render();
        }, 1);

        // Render the "Switch to New View" button on the legacy subscriptions page
        add_action('admin_head', function () {
            if (!SubscriptionsAdminPage::isShowing()) {
                return;
            }

            if (!$this->shouldShowLegacySubscriptionsPage()) {
                return;
            }

            give(SubscriptionsAdminPage::class)->renderReactSwitch();
        });
    }

    /**
     * @since 4.14.0
     */
    private function shouldShowLegacySubscriptionsPage(): bool
    {
        $userId = get_current_user_id();

        return (bool) get_user_meta($userId, '_give_subscriptions_archive_show_legacy', true);
    }

    /**
     * Legacy Listeners
     *
     * @since 2.19.6
     */
    private function bootLegacyListeners()
    {
        Hooks::addAction('givewp_subscription_creating', DispatchGiveSubscriptionPreCreate::class);
        Hooks::addAction('givewp_subscription_created', DispatchGiveSubscriptionPostCreate::class);
    }

    /**
     * Registers database migrations with the MigrationsRunner
     *
     * @since 4.11.0 add AddCampaignIdColumn and AddCampaignId migrations
     * @since 2.24.0
     */
    private function registerMigrations()
    {
        /** @var MigrationsRegister $register */
        $register = give(MigrationsRegister::class);
        $register->addMigrations([
            CreateSubscriptionTables::class,
            AddPaymentModeToSubscriptionTable::class,
            BackfillMissingCampaignIdForDonations::class,
            AddCampaignIdColumn::class,
            AddCampaignId::class,
            UpdateProductID::class
        ]);
    }

    /**
     * @since 4.8.0
     */
    private function registerSubscriptionAdminOptions()
    {
        add_action('admin_enqueue_scripts', function() {
            if (SubscriptionsAdminPage::isShowing()) {
                give(LoadSubscriptionAdminOptions::class)();
            }
        });
    }
}
