<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Harbor;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;
use Give\VendorOverrides\Harbor\Actions\ReportLegacyLicences;
use Give\Vendors\LiquidWeb\Harbor\Config;
use Give\Vendors\LiquidWeb\Harbor\Harbor;

/**
 * @unreleased
 */
class HarborServiceProvider implements ServiceProviderContract
{
    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function register()
    {
        Config::set_container(give()->getContainer());

        Harbor::init();
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addFilter('stellarwp/harbor/legacy_licenses', ReportLegacyLicences::class);

        remove_action('admin_notices', 'give_license_notices', 10);

        lw_harbor_register_submenu('edit.php?post_type=give_forms');
    }

}
