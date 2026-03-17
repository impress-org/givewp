<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Uplink;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;
use Give\VendorOverrides\Uplink\Actions\RegisterProduct;
use Give\VendorOverrides\Uplink\Actions\ReportLegacyLicences;
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

        Uplink::init();
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addFilter('stellarwp/uplink/product_registry', RegisterProduct::class);

        Hooks::addFilter('stellarwp/uplink/legacy_licenses', ReportLegacyLicences::class);

        remove_action('admin_notices', 'give_license_notices', 10);
    }

}
