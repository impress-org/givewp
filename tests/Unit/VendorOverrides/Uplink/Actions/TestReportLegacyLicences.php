<?php

declare(strict_types=1);

namespace Give\Tests\Unit\VendorOverrides\Uplink\Actions;

use Give\License\Repositories\LicenseRepository;
use Give\License\ValueObjects\LicenseOptionKeys;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;
use Give\VendorOverrides\Uplink\Actions\ReportLegacyLicences;

/**
 * @since 4.15.0
 * @coversDefaultClass \Give\VendorOverrides\Uplink\Actions\ReportLegacyLicences
 */
class TestReportLegacyLicences extends TestCase
{
    use HasLicenseData;
    use RefreshDatabase;

    private ReportLegacyLicences $action;

    /**
     * @since 4.15.0
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->action = new ReportLegacyLicences(new LicenseRepository());
    }

    /**
     * @since 4.15.0
     */
    public function testReportedLicenseShape(): void
    {
        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData([
                'license_key' => 'license-key-1234567890',
                'license'     => 'valid',
                'expires'     => '2025-06-19 23:59:59',
                'download'    => [
                    [
                        'index'           => '0',
                        'attachment_id'   => '0',
                        'thumbnail_size'  => '',
                        'name'            => 'Give Recurring',
                        'file'            => 'https://givewp.com',
                        'condition'       => 'all',
                        'array_index'     => 0,
                        'plugin_slug'     => 'give-recurring',
                        'readme'          => 'https://givewp.com/downloads/plugins/give-recurring/readme.txt',
                        'current_version' => '1.5.0',
                    ],
                ],
            ]),
        ]);

        $result = ($this->action)([]);

        $this->assertSame([
            [
                'key'        => 'license-key-1234567890',
                'slug'       => 'give-recurring',
                'name'       => 'Give Recurring',
                'brand'      => 'give',
                'status'     => 'valid',
                'page_url'   => admin_url('edit.php?post_type=give_forms&page=give-settings&tab=licenses'),
                'expires_at' => '2025-06-19 23:59:59',
            ],
        ], $result);
    }

    /**
     * @since 4.15.0
     */
    public function testReturnsEmptyWhenNoLicensesAreStored(): void
    {
        $result = ($this->action)([]);

        $this->assertSame([], $result);
    }

    /**
     * @since 4.15.0
     */
    public function testPreservesIncomingLicensesFromFilter(): void
    {
        $existing = [
            ['key' => 'other-plugin-key', 'slug' => 'other-plugin', 'name' => 'Other Plugin', 'brand' => 'other', 'status' => 'valid', 'page_url' => 'https://example.com'],
        ];

        $result = ($this->action)($existing);

        $this->assertCount(1, $result);
        $this->assertSame($existing[0], $result[0]);
    }

    /**
     * Each download in a license becomes its own uplink legacy license entry,
     * with the parent license's key as the credential.
     *
     * @since 4.15.0
     */
    public function testEachDownloadBecomesASeparateUplinkEntry(): void
    {
        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData([
                'license_key' => 'license-key-1234567890',
                'license' => 'valid',
                'expires' => '2025-06-19 23:59:59',
            ]),
        ]);

        $result = ($this->action)([]);

        // The default fixture has 3 downloads: give-manual-donations, give-pdf-receipts, give-stripe
        $this->assertCount(3, $result);

        $slugs = array_column($result, 'slug');
        $this->assertContains('give-manual-donations', $slugs);
        $this->assertContains('give-pdf-receipts', $slugs);
        $this->assertContains('give-stripe', $slugs);
    }

    /**
     * @since 4.15.0
     */
    public function testEachEntryContainsCorrectLicenseKey(): void
    {
        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData([
                'license_key' => 'license-key-1234567890',
            ]),
        ]);

        $result = ($this->action)([]);

        foreach ($result as $entry) {
            $this->assertSame('license-key-1234567890', $entry['key']);
        }
    }

    /**
     * @since 4.15.0
     */
    public function testEntryContainsCorrectStatus(): void
    {
        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData([
                'license_key' => 'license-key-1234567890',
                'license' => 'expired',
            ]),
        ]);

        $result = ($this->action)([]);

        foreach ($result as $entry) {
            $this->assertSame('expired', $entry['status']);
        }
    }

    /**
     * @since 4.15.0
     */
    public function testEntryIncludesExpiresAtWhenLicenseHasExpiry(): void
    {
        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData([
                'license_key' => 'license-key-1234567890',
                'expires' => '2025-06-19 23:59:59',
            ]),
        ]);

        $result = ($this->action)([]);

        foreach ($result as $entry) {
            $this->assertArrayHasKey('expires_at', $entry);
            $this->assertSame('2025-06-19 23:59:59', $entry['expires_at']);
        }
    }

    /**
     * @since 4.15.0
     */
    public function testEntryOmitsExpiresAtWhenLicenseHasNoExpiry(): void
    {
        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData([
                'license_key' => 'license-key-1234567890',
                'expires' => '',
            ]),
        ]);

        $result = ($this->action)([]);

        foreach ($result as $entry) {
            $this->assertArrayNotHasKey('expires_at', $entry);
        }
    }

    /**
     * @since 4.15.0
     */
    public function testDownloadsWithoutPluginSlugAreSkipped(): void
    {
        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData([
                'license_key' => 'license-key-1234567890',
                'download' => [
                    [
                        'index' => '0',
                        'attachment_id' => '0',
                        'thumbnail_size' => '',
                        'name' => 'No Slug Plugin',
                        'file' => 'https://givewp.com',
                        'condition' => 'all',
                        'array_index' => 0,
                        'plugin_slug' => '',
                        'readme' => '',
                        'current_version' => '1.0.0',
                    ],
                    [
                        'index' => '0',
                        'attachment_id' => '0',
                        'thumbnail_size' => '',
                        'name' => 'Give Recurring',
                        'file' => 'https://givewp.com',
                        'condition' => 'all',
                        'array_index' => 1,
                        'plugin_slug' => 'give-recurring',
                        'readme' => '',
                        'current_version' => '1.0.0',
                    ],
                ],
            ]),
        ]);

        $result = ($this->action)([]);

        $this->assertCount(1, $result);
        $this->assertSame('give-recurring', $result[0]['slug']);
    }

    /**
     * Multiple stored licenses each expand their own downloads independently.
     *
     * @since 4.15.0
     */
    public function testMultipleLicensesExpandToIndependentEntries(): void
    {
        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1' => $this->getRawLicenseData([
                'license_key' => 'license-key-1',
                'download' => [
                    ['index' => '0', 'attachment_id' => '0', 'thumbnail_size' => '', 'name' => 'Give Recurring', 'file' => '', 'condition' => 'all', 'array_index' => 0, 'plugin_slug' => 'give-recurring', 'readme' => '', 'current_version' => '1.0.0'],
                ],
            ]),
            'license-key-2' => $this->getRawLicenseData([
                'license_key' => 'license-key-2',
                'download' => [
                    ['index' => '0', 'attachment_id' => '0', 'thumbnail_size' => '', 'name' => 'Give Stripe', 'file' => '', 'condition' => 'all', 'array_index' => 0, 'plugin_slug' => 'give-stripe', 'readme' => '', 'current_version' => '2.0.0'],
                ],
            ]),
        ]);

        $result = ($this->action)([]);

        $this->assertCount(2, $result);

        $bySlug = array_column($result, null, 'slug');
        $this->assertSame('license-key-1', $bySlug['give-recurring']['key']);
        $this->assertSame('license-key-2', $bySlug['give-stripe']['key']);
    }

    /**
     * @since 4.15.0
     */
    public function testEntryBrandIsAlwaysGive(): void
    {
        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData(),
        ]);

        $result = ($this->action)([]);

        foreach ($result as $entry) {
            $this->assertSame('give', $entry['brand']);
        }
    }
}
