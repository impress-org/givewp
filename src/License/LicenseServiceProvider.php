<?php

namespace Give\License;

use Give\License\Repositories\LicenseRepository;
use Give\ServiceProviders\ServiceProvider;

class LicenseServiceProvider implements ServiceProvider
{
    /**
     * @since 2.11.3
     */
    public function register()
    {
        give()->singleton(PremiumAddonsListManager::class);
        give()->singleton(LicenseRepository::class);
    }

    /**
     * @since 2.11.3
     */
    public function boot()
    {
    }
}
