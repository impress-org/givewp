<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Harbor;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;
use Give\VendorOverrides\Harbor\Actions\HasActivePremiumAddons;
use Give\VendorOverrides\Harbor\Actions\ReportLegacyLicences;
use Give\Vendors\LiquidWeb\Harbor\Config;
use Give\Vendors\LiquidWeb\Harbor\Harbor;

/**
 * @since 4.15.0
 */
class HarborServiceProvider implements ServiceProviderContract
{
    /**
     * @since 4.15.2 Register lw_harbor/premium_plugin_exists filter
     * @since 4.15.0
     *
     * @inheritDoc
     */
    public function register()
    {
        Config::set_plugin_basename(GIVE_PLUGIN_BASENAME);
        Config::set_container(give()->getContainer());

        // reports whether any GiveWP premium add-on is active to Harbor
        Hooks::addFilter('lw_harbor/premium_plugin_exists', HasActivePremiumAddons::class);

        Harbor::init();
    }

    /**
     * @since 4.15.0
     *
     * @inheritDoc
     */
    public function boot()
    {
        // reports legacy licenses to Harbor
        Hooks::addFilter('stellarwp/harbor/legacy_licenses', ReportLegacyLicences::class);

        // adds a "licensing" submenu to Give
        lw_harbor_register_submenu('edit.php?post_type=give_forms');

        // Fixes Activation banner doesn't appear when a Give Addon is activated from Harbor.
        add_action('rest_api_init', array($this, 'loadGivePluginFile'));
    }

    /**
     * Load the plugin file so that activation banner appears when
     * a Give Addon is activated from Harbor.
     *
     * @since TBD
     *
     * @return void
     */
    public function loadGivePluginFile()
    {
        $file = GIVE_PLUGIN_DIR . 'includes/admin/plugins.php';
        if (file_exists($file)) {
            include_once $file;
        }
    }
}
