<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Harbor\Actions;

use Give\License\Repositories\LicenseRepository;

/**
 * Reports legacy Give licenses to Harbor by expanding each license's downloads
 * into individual entries, so each plugin slug (e.g. "give-recurring") is
 * associated with the correct license key.
 *
 * Hooked into the `stellarwp/harbor/legacy_licenses` filter.
 *
 * @unreleased
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
        $pageUrl = admin_url('edit.php?post_type=give_forms&page=give-settings&tab=licenses');
        $legacyLicenses = [];
        $coveredSlugs = [];

        foreach ($storedLicenses as $license) {
            foreach ($license->downloads as $download) {
                if (empty($download->pluginSlug)) {
                    continue;
                }

                $coveredSlugs[] = $download->pluginSlug;

                $entry = [
                    'key'       => $license->licenseKey,
                    'slug'      => $download->pluginSlug,
                    'name'      => $download->name,
                    'brand'     => 'give',
                    'is_active' => $license->isActive,
                    'page_url'  => $pageUrl,
                ];

                if (!empty($license->expires)) {
                    $entry['expires_at'] = $license->expires;
                }

                $legacyLicenses[] = $entry;
            }
        }

        foreach ($this->getUnlicensedPremiumAddons($coveredSlugs, $pageUrl) as $entry) {
            $legacyLicenses[] = $entry;
        }

        return array_merge($licenses, $legacyLicenses);
    }

    /**
     * Returns entries for premium add-ons that are installed but have no license key in the database.
     */
    private function getUnlicensedPremiumAddons(array $coveredSlugs, string $pageUrl): array
    {
        $entries = [];

        foreach (give_get_plugins(['only_premium_add_ons' => true]) as $plugin) {
            $slug = $plugin['Dir'];

            if (in_array($slug, $coveredSlugs, true)) {
                continue;
            }

            $entries[] = [
                'key'       => '',
                'slug'      => $slug,
                'name'      => $plugin['Name'],
                'brand'     => 'give',
                'is_active' => false,
                'page_url'  => $pageUrl,
            ];
        }

        return $entries;
    }
}
