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
