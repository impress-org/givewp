<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Uplink;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;
use Give\VendorOverrides\Uplink\Actions\RegisterProduct;
use Give\VendorOverrides\Uplink\Actions\ReportLegacyLicences;
use Give\VendorOverrides\Uplink\Actions\SuppressLegacyLicenses;
use Give\Vendors\StellarWP\Uplink\Config;
use Give\Vendors\StellarWP\Uplink\Uplink;

class UplinkServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        Config::set_container(give()->getContainer());
        Config::set_hook_prefix('givewp_');

        Uplink::init();
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addFilter('stellarwp/uplink/product_registry', RegisterProduct::class);

        Hooks::addFilter('stellarwp/uplink/legacy_licenses', ReportLegacyLicences::class);

        Hooks::addAction('admin_init', SuppressLegacyLicenses::class);
    }

}
