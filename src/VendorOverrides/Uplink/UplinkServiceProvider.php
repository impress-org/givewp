<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Uplink;

use Give;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;
use Give\Vendors\StellarWP\Uplink\Config;
use Give\Vendors\StellarWP\Uplink\Register;
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
        add_filter('stellarwp/uplink/legacy_licenses', function (array $licenses) {
            $pageUrl = admin_url('edit.php?post_type=give_forms&page=give-settings&tab=licenses');

            return array_merge($licenses, [
                [
                    'key' => 'license-key-1',
                    'slug' => 'give-recurring',
                    'name' => 'Give Recurring',
                    'brand' => 'give',
                    'status' => 'valid',
                    'page_url' => $pageUrl,
                ],
                [
                    'key' => 'license-key-2',
                    'slug' => 'give-form-field-manager',
                    'name' => 'Give Form Field Manager',
                    'brand' => 'give',
                    'status' => 'expired',
                    'expires_at' => '2026-01-01',
                    'page_url' => $pageUrl,
                ]
            ]);
        }, 10, 1);

        /**
         * Fix duplicate class bug: the field template puts "stellarwp-uplink-license-key-field" on both the <tr> wrapper and the inner <div> with data attributes, causing the JS to crash when it matches the <tr> (which has no data-action).
         * @see PR: https://github.com/stellarwp/uplink/pull/104
         */
        add_filter('stellarwp/uplink/' . Config::get_hook_prefix() . '/license_field_html', static function (string $html): string {
            return str_replace(
                '<tr class="stellarwp-uplink-license-key-field">',
                '<tr class="stellarwp-uplink-license-key-field-row">',
                $html
            );
        });

        //$this->suppress_legacy_licenses();
    }

    private function suppress_legacy_licenses()
    {

        remove_action('admin_notices', 'give_license_notices', 10);
        remove_filter('pre_set_site_transient_update_plugins', 'give_check_addon_updates', 999);
        remove_action('give_thricely_scheduled_events', 'give_refresh_licenses', 10);
        wp_unschedule_hook('give_thricely_scheduled_events');
    }
}
