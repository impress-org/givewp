<?php

namespace Give\Subscriptions;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Subscriptions\LegacyListeners\DispatchGiveSubscriptionPostCreate;
use Give\Subscriptions\LegacyListeners\DispatchGiveSubscriptionPreCreate;
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
    }

    /**
     * Legacy Listeners
     *
     * @unreleased
     */
    private function bootLegacyListeners()
    {
        Hooks::addAction('give_subscription_creating', DispatchGiveSubscriptionPreCreate::class);
        Hooks::addAction('give_subscription_created', DispatchGiveSubscriptionPostCreate::class);
    }
}
