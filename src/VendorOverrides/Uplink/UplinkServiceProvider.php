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

        add_filter('stellarwp/uplink/legacy_licenses', function($licenses) {
            return array_merge($licenses, [
                [
                    'key' => 'give-recurring-license-key',
                    'slug' => 'give-recurring',
                    'name' => 'Give Recurring',
                    'brand' => 'give',
                    'is_active' => false,
                    'page_url' => admin_url('edit.php?post_type=give_forms&page=give-settings&tab=licenses'),
                    'expires_at' => '2026-01-01',
                ]
            ]);
        });
    }

}
