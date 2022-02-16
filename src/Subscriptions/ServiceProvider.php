<?php

namespace Give\Subscriptions;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
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
    }
}
