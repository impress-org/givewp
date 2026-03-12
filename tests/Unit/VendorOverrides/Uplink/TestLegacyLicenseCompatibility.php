<?php

declare(strict_types=1);

namespace Give\Tests\Unit\VendorOverrides\Uplink;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;
use Give\License\ValueObjects\LicenseOptionKeys;

/**
 * Tests that legacy license functions correctly defer to Uplink when a unified
 * license is active, while continuing to serve users who have legacy licenses.
 *
 * @unreleased
 */
class TestLegacyLicenseCompatibility extends TestCase
{
    use HasLicenseData;
    use RefreshDatabase;

    /**
     * A version key that will never clash with a real Uplink version.
     */
    private const TEST_VERSION = '99.0.0-test';

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->setUplinkProductActive(false);
        $this->setUplinkFeatureAvailable([]);

        add_filter('stellarwp/uplink/highest_version', [$this, 'filterHighestVersion'], 99);
    }

    /**
     * @unreleased
     */
    public function tearDown(): void
    {
        remove_filter('stellarwp/uplink/highest_version', [$this, 'filterHighestVersion'], 99);

        parent::tearDown();
    }

    /**
     * @unreleased
     */
    public function filterHighestVersion(): string
    {
        return self::TEST_VERSION;
    }

    /**
     * @unreleased
     */
    private function setUplinkProductActive(bool $active): void
    {
        _stellarwp_uplink_global_function_registry(
            'stellarwp_uplink_is_product_license_active',
            self::TEST_VERSION,
            static function (string $product) use ($active): bool {
                return $active && $product === 'give';
            }
        );
    }

    /**
     * @unreleased
     *
     * @param string[] $slugs Add-on slugs available via Uplink.
     */
    private function setUplinkFeatureAvailable(array $slugs): void
    {
        _stellarwp_uplink_global_function_registry(
            'stellarwp_uplink_is_feature_available',
            self::TEST_VERSION,
            static function (string $slug) use ($slugs): bool {
                return in_array($slug, $slugs, true);
            }
        );
    }

    // -------------------------------------------------------------------------
    // give_check_addon_updates
    // -------------------------------------------------------------------------

    /**
     * New Uplink customer with no legacy licenses: update check should be skipped.
     *
     * @unreleased
     */
    public function testCheckAddonUpdatesReturnsEarlyWhenUplinkActiveAndNoLegacyLicenses(): void
    {
        $this->setUplinkProductActive(true);

        $transient = new \stdClass();
        $transient->checked = ['some-plugin/plugin.php' => '1.0.0'];

        $result = give_check_addon_updates($transient);

        $this->assertSame($transient, $result, 'Transient should be returned unchanged when Uplink is active and no legacy licenses exist');
    }

    /**
     * Mixed customer: Uplink active but legacy licenses still exist.
     * Update check must continue running for the legacy-licensed add-ons.
     *
     * @unreleased
     */
    public function testCheckAddonUpdatesRunsNormallyWhenLegacyLicensesExist(): void
    {
        $this->setUplinkProductActive(true);

        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData(),
        ]);

        // The function will reach give_refresh_licenses internally (no cached
        // give_get_versions), which returns [] without making an HTTP call
        // because the legacy API request will fail in the test environment.
        // What matters is that give_check_addon_updates does NOT return early.
        $transient = new \stdClass();
        $called = false;

        add_filter('pre_http_request', static function () use (&$called) {
            $called = true;
            return new \WP_Error('test', 'Blocked HTTP in tests');
        }, 10, 3);

        give_check_addon_updates($transient);

        remove_all_filters('pre_http_request');

        $this->assertTrue($called, 'Legacy license API should be called when legacy licenses exist');
    }

    // -------------------------------------------------------------------------
    // give_refresh_licenses
    // -------------------------------------------------------------------------

    /**
     * New Uplink customer: refresh should be skipped entirely.
     *
     * @unreleased
     */
    public function testRefreshLicensesReturnsEarlyWhenUplinkActiveAndNoLegacyLicenses(): void
    {
        $this->setUplinkProductActive(true);

        $httpCalled = false;
        add_filter('pre_http_request', static function () use (&$httpCalled) {
            $httpCalled = true;
            return new \WP_Error('test', 'Blocked HTTP in tests');
        }, 10, 3);

        $result = give_refresh_licenses();

        remove_all_filters('pre_http_request');

        $this->assertSame([], $result, 'Should return empty array without calling the legacy API');
        $this->assertFalse($httpCalled, 'Legacy license API should not be called when Uplink is active and no legacy licenses exist');
    }

    /**
     * Mixed customer: Uplink active but legacy licenses exist.
     * Refresh must still call the legacy API to update those licenses.
     *
     * @unreleased
     */
    public function testRefreshLicensesCallsApiWhenLegacyLicensesExist(): void
    {
        $this->setUplinkProductActive(true);

        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData(),
        ]);

        $httpCalled = false;
        add_filter('pre_http_request', static function () use (&$httpCalled) {
            $httpCalled = true;
            return new \WP_Error('test', 'Blocked HTTP in tests');
        }, 10, 3);

        give_refresh_licenses();

        remove_all_filters('pre_http_request');

        $this->assertTrue($httpCalled, 'Legacy license API should be called when legacy licenses exist');
    }

    // -------------------------------------------------------------------------
    // Give_License::get_license_by_plugin_dirname
    // -------------------------------------------------------------------------

    /**
     * Add-on covered by Uplink with no legacy license should be reported as licensed.
     *
     * @unreleased
     */
    public function testGetLicenseByPluginDirnameReturnsValidWhenUplinkCoversAddon(): void
    {
        $this->setUplinkFeatureAvailable(['give-recurring']);

        $result = \Give_License::get_license_by_plugin_dirname('give-recurring');

        $this->assertNotEmpty($result, 'Should return a non-empty license array for Uplink-covered add-on');
        $this->assertSame('valid', $result['license'], 'License status should be valid for Uplink-covered add-on');
    }

    /**
     * Add-on not covered by either legacy or Uplink should be reported as unlicensed.
     *
     * @unreleased
     */
    public function testGetLicenseByPluginDirnameReturnsEmptyWhenNotCovered(): void
    {
        $this->setUplinkFeatureAvailable([]);

        $result = \Give_License::get_license_by_plugin_dirname('give-recurring');

        $this->assertEmpty($result, 'Should return empty array when add-on is not covered by legacy or Uplink');
    }

    /**
     * Legacy license takes precedence over Uplink stub when both cover the same add-on.
     *
     * @unreleased
     */
    public function testGetLicenseByPluginDirnamePreservesLegacyLicenseWhenBothExist(): void
    {
        $this->setUplinkFeatureAvailable(['give-stripe']);

        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData(),
        ]);

        $result = \Give_License::get_license_by_plugin_dirname('give-stripe');

        $this->assertArrayHasKey('license_key', $result, 'Legacy license data should be returned when a legacy license exists');
        $this->assertSame('license-key-1234567890', $result['license_key']);
    }
}
