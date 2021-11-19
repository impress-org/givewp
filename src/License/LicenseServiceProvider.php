<?php

namespace Give\License;

use Give\ServiceProviders\ServiceProvider;

class LicenseServiceProvider implements ServiceProvider
{
    /**
     * @since 2.11.3
     */
    public function register()
    {
        give()->singleton(PremiumAddonsListManager::class);
    }

    /**
     * @since 2.11.3
     */
    public function boot()
    {
    }
}
