<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Uplink\Actions;

/**
 * @unreleased
 */
class SuppressLegacyLicenses
{
    /**
     * Suppress legacy licenses when a unified license key is present.
     *
     * TODO: This is a temporary solution to suppress legacy licenses when a unified license key is present. This should probably be handled within each hook.
     *
     * @unreleased
     */
    public function __invoke()
    {
        if (stellarwp_uplink_has_unified_license_key() && stellarwp_uplink_is_product_license_active('give')) {
            remove_action('admin_notices', 'give_license_notices', 10);
            remove_filter('pre_set_site_transient_update_plugins', 'give_check_addon_updates', 999);
            remove_action('give_thricely_scheduled_events', 'give_refresh_licenses', 10);
            wp_unschedule_hook('give_thricely_scheduled_events');
        }
    }
}
