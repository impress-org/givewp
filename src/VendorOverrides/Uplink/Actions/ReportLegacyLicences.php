<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Uplink\Actions;

use Give\License\Repositories\LicenseRepository;

/**
 * Reports legacy Give licenses to Uplink by expanding each license's downloads
 * into individual entries, so each plugin slug (e.g. "give-recurring") is
 * associated with the correct license key.
 *
 * Hooked into the `stellarwp/uplink/legacy_licenses` filter.
 */
class ReportLegacyLicences
{
    private LicenseRepository $licenseRepository;

    public function __construct(LicenseRepository $licenseRepository)
    {
        $this->licenseRepository = $licenseRepository;
    }

    /**
     * @param array $licenses Existing licenses already added by other plugins.
     * @return array
     */
    public function __invoke(array $licenses): array
    {
        $storedLicenses = $this->licenseRepository->getLicenses();

        if (empty($storedLicenses)) {
            return $licenses;
        }

        $pageUrl = admin_url('edit.php?post_type=give_forms&page=give-settings&tab=licenses');
        $legacyLicenses = [];

        foreach ($storedLicenses as $license) {
            foreach ($license->downloads as $download) {
                if (empty($download->pluginSlug)) {
                    continue;
                }

                $entry = [
                    'key'      => $license->licenseKey,
                    'slug'     => $download->pluginSlug,
                    'name'     => $download->name,
                    'brand'    => 'give',
                    'status'   => $license->license,
                    'page_url' => $pageUrl,
                ];

                if (!empty($license->expires)) {
                    $entry['expires_at'] = $license->expires;
                }

                $legacyLicenses[] = $entry;
            }
        }

        return array_merge($licenses, $legacyLicenses);
    }
}
