<?php

namespace Give\Subscriptions;

use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Subscriptions\Actions\LoadSubscriptionAdminOptions;
use Give\Subscriptions\Actions\RegisterSubscriptionEntity;
use Give\Subscriptions\LegacyListeners\DispatchGiveSubscriptionPostCreate;
use Give\Subscriptions\LegacyListeners\DispatchGiveSubscriptionPreCreate;
use Give\Subscriptions\ListTable\SubscriptionsListTable;
use Give\Subscriptions\Migrations\AddPaymentModeToSubscriptionTable;
use Give\Subscriptions\Migrations\BackfillMissingCampaignIdForDonations;
use Give\Subscriptions\Migrations\CreateSubscriptionTables;
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
     */
    public function boot()
    {
        $this->bootLegacyListeners();
        $this->registerMigrations();
        $this->registerSubscriptionEntity();
        $this->registerSubscriptionAdminOptions();

        $userId = get_current_user_id();
        $showLegacy = get_user_meta($userId, '_give_subscriptions_archive_show_legacy', true);
        // only register new admin page if user hasn't chosen to use the old one
        if (empty($showLegacy) && SubscriptionsAdminPage::isShowing()) {
            Hooks::addAction('give_forms_page_give-subscriptions', SubscriptionsAdminPage::class, 'render', 1);
        } elseif (SubscriptionsAdminPage::isShowing()) {
            Hooks::addAction('admin_head', SubscriptionsAdminPage::class, 'renderReactSwitch');
        }
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
        ]);
    }

    /**
     * @since 4.8.0
     */
    private function registerSubscriptionEntity()
    {
        Hooks::addAction('init', RegisterSubscriptionEntity::class);
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
