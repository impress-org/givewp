<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Uplink\Actions;

use Give\License\Repositories\LicenseRepository;

/**
 * @unreleased
 *
 * TODO: This is a temporary solution to suppress legacy licenses when a unified license key is present. This should probably be handled within each hook.
 */
class SuppressLegacyLicenses
{
    private LicenseRepository $licenseRepository;

    /**
     * @unreleased
     */
    public function __construct(LicenseRepository $licenseRepository)
    {
        $this->licenseRepository = $licenseRepository;
    }

    /**
     * Suppress legacy license functionality when a unified Uplink license key is active.
     *
     * Admin notices are always suppressed when Uplink is active — Uplink handles license status UI.
     * Update checks and cron are only suppressed when no legacy licenses exist in the database,
     * since existing customers may have add-on licenses that still require update notifications.
     *
     * @unreleased
     */
    public function __invoke()
    {
        // Only suppress legacy behavior when Uplink is fully active:
        // a unified key must exist AND Give's license must be valid.
        // If either condition fails (no key, expired, or grace period elapsed),
        // the legacy system should continue running unmodified.
        if (!stellarwp_uplink_has_unified_license_key() || !stellarwp_uplink_is_product_license_active('give')) {
            return;
        }

        // Always suppress legacy admin notices — Uplink handles license status notifications
        remove_action('admin_notices', 'give_license_notices', 10);

        // Only suppress update checks and cron when no legacy licenses exist.
        // If legacy licenses are present, we must keep updating those add-ons.
        if (!$this->licenseRepository->hasStoredLicenses()) {
            remove_filter('pre_set_site_transient_update_plugins', 'give_check_addon_updates', 999);
            remove_action('give_thricely_scheduled_events', 'give_refresh_licenses', 10);
            wp_unschedule_hook('give_thricely_scheduled_events');
        }
    }
}
