<?php

namespace Give\Subscriptions;

use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Subscriptions\LegacyListeners\DispatchGiveSubscriptionPostCreate;
use Give\Subscriptions\LegacyListeners\DispatchGiveSubscriptionPreCreate;
use Give\Subscriptions\Migrations\CreateSubscriptionTables;
use Give\Subscriptions\Repositories\SubscriptionRepository;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('subscriptions', SubscriptionRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $this->bootLegacyListeners();

        give(MigrationsRegister::class)->addMigration(CreateSubscriptionTables::class);

        $userId = get_current_user_id();
        $showLegacy = get_user_meta($userId, '_give_subscriptions_archive_show_legacy', true);
        // only register new admin page if user hasn't chosen to use the old one
        if (empty($showLegacy) && SubscriptionsAdminPage::isShowing()) {
            Hooks::addAction('admin_enqueue_scripts', SubscriptionsAdminPage::class, 'loadScripts');
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
}
