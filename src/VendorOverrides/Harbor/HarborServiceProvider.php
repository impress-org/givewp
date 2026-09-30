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
     * @since TBD Skip Harbor when the providers load after wp_loaded.
     * @since 4.15.2 Register lw_harbor/premium_plugin_exists filter
     * @since 4.15.0
     *
     * @inheritDoc
     */
    public function register()
    {
        if ($this->loadsTooLateForHarbor()) {
            return;
        }

        Config::set_plugin_basename(GIVE_PLUGIN_BASENAME);
        Config::set_container(give()->getContainer());

        // reports whether any GiveWP premium add-on is active to Harbor
        Hooks::addFilter('lw_harbor/premium_plugin_exists', HasActivePremiumAddons::class);

        Harbor::init();
    }

    /**
     * @since TBD Skip Harbor when the providers load after wp_loaded.
     * @since 4.15.0
     *
     * @inheritDoc
     */
    public function boot()
    {
        if ($this->loadsTooLateForHarbor()) {
            return;
        }

        // reports legacy licenses to Harbor
        Hooks::addFilter('stellarwp/harbor/legacy_licenses', ReportLegacyLicences::class);

        // adds a "licensing" submenu to Give
        lw_harbor_register_submenu('edit.php?post_type=give_forms');
    }

    /**
     * Harbor only accepts instance registrations before wp_loaded and reports anything later with
     * _doing_it_wrong(). WordPress runs plugin activation after wp_loaded, and activation loads the
     * service providers, so Harbor::init() would only raise that notice and then be ignored. Harbor
     * is a shared library, so skipping it here does not affect other plugins that bundle it.
     *
     * @since TBD
     */
    private function loadsTooLateForHarbor(): bool
    {
        return (bool) did_action('wp_loaded');
    }
}
