<?php

declare(strict_types=1);

namespace Give\Multisite;

use Give\Helpers\Hooks;
use Give\Multisite\Actions\DeleteSiteTables;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;

class ServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('wp_delete_site', DeleteSiteTables::class);
    }
}
