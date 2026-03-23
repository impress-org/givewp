<?php

declare(strict_types=1);

namespace Give\Tests\Unit\VendorOverrides\Harbor;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;
use Give\License\ValueObjects\LicenseOptionKeys;

/**
 * Tests that legacy license functions correctly defer to Harbor when a unified
 * license is active, while continuing to serve users who have legacy licenses.
 *
 * @unreleased
 */
class TestLegacyLicenseCompatibility extends TestCase
{
    use HasLicenseData;
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();

        HarborStubs::reset();
    }

    /**
     * @unreleased
     */
    public function tearDown(): void
    {
        HarborStubs::reset();

        parent::tearDown();
    }

    /**
     * @unreleased
     */
    private function setHarborProductActive(bool $active): void
    {
        HarborStubs::$productActive = $active;
    }

    /**
     * @unreleased
     *
     * @param string[] $slugs Add-on slugs available via Harbor.
     */
    private function setHarborFeatureAvailable(array $slugs): void
    {
        HarborStubs::$availableFeatures = $slugs;
    }

    // -------------------------------------------------------------------------
    // give_check_addon_updates
    // -------------------------------------------------------------------------

    /**
     * New Harbor customer with no legacy licenses: update check should be skipped.
     *
     * @unreleased
     */
    public function testCheckAddonUpdatesReturnsEarlyWhenHarborActiveAndNoLegacyLicenses(): void
    {
        $this->setHarborProductActive(true);

        $transient = new \stdClass();
        $transient->checked = ['some-plugin/plugin.php' => '1.0.0'];

        $result = give_check_addon_updates($transient);

        $this->assertSame($transient, $result, 'Transient should be returned unchanged when Harbor is active and no legacy licenses exist');
    }

    /**
     * Mixed customer: Harbor active but legacy licenses still exist.
     * Update check must continue running for the legacy-licensed add-ons.
     *
     * @unreleased
     */
    public function testCheckAddonUpdatesRunsNormallyWhenLegacyLicensesExist(): void
    {
        $this->setHarborProductActive(true);

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
     * New Harbor customer: refresh should be skipped entirely.
     *
     * @unreleased
     */
    public function testRefreshLicensesReturnsEarlyWhenHarborActiveAndNoLegacyLicenses(): void
    {
        $this->setHarborProductActive(true);

        $httpCalled = false;
        add_filter('pre_http_request', static function () use (&$httpCalled) {
            $httpCalled = true;
            return new \WP_Error('test', 'Blocked HTTP in tests');
        }, 10, 3);

        $result = give_refresh_licenses();

        remove_all_filters('pre_http_request');

        $this->assertSame([], $result, 'Should return empty array without calling the legacy API');
        $this->assertFalse($httpCalled, 'Legacy license API should not be called when Harbor is active and no legacy licenses exist');
    }

    /**
     * Mixed customer: Harbor active but legacy licenses exist.
     * Refresh must still call the legacy API to update those licenses.
     *
     * @unreleased
     */
    public function testRefreshLicensesCallsApiWhenLegacyLicensesExist(): void
    {
        $this->setHarborProductActive(true);

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
     * Add-on covered by Harbor with no legacy license should be reported as licensed.
     *
     * @unreleased
     */
    public function testGetLicenseByPluginDirnameReturnsValidWhenHarborCoversAddon(): void
    {
        $this->setHarborFeatureAvailable(['give-recurring']);

        $result = \Give_License::get_license_by_plugin_dirname('give-recurring');

        $this->assertNotEmpty($result, 'Should return a non-empty license array for Harbor-covered add-on');
        $this->assertSame('valid', $result['license'], 'License status should be valid for Harbor-covered add-on');
    }

    /**
     * Add-on not covered by either legacy or Harbor should be reported as unlicensed.
     *
     * @unreleased
     */
    public function testGetLicenseByPluginDirnameReturnsEmptyWhenNotCovered(): void
    {
        $this->setHarborFeatureAvailable([]);

        $result = \Give_License::get_license_by_plugin_dirname('give-recurring');

        $this->assertEmpty($result, 'Should return empty array when add-on is not covered by legacy or Harbor');
    }

    /**
     * Legacy license takes precedence over Harbor stub when both cover the same add-on.
     *
     * @unreleased
     */
    public function testGetLicenseByPluginDirnamePreservesLegacyLicenseWhenBothExist(): void
    {
        $this->setHarborFeatureAvailable(['give-stripe']);

        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData(['plugin_slug' => 'give-stripe']),
        ]);

        $result = \Give_License::get_license_by_plugin_dirname('give-stripe');

        $this->assertArrayHasKey('license_key', $result, 'Legacy license data should be returned when a legacy license exists');
        $this->assertSame('license-key-1234567890', $result['license_key']);
    }
}
