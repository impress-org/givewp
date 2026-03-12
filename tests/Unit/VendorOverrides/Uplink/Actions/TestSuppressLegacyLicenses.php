<?php

declare(strict_types=1);

namespace Give\Tests\Unit\VendorOverrides\Uplink\Actions;

use Give\License\Repositories\LicenseRepository;
use Give\License\ValueObjects\LicenseOptionKeys;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;
use Give\VendorOverrides\Uplink\Actions\SuppressLegacyLicenses;

/**
 * @unreleased
 * @coversDefaultClass \Give\VendorOverrides\Uplink\Actions\SuppressLegacyLicenses
 */
class TestSuppressLegacyLicenses extends TestCase
{
    use HasLicenseData;
    use RefreshDatabase;

    /**
     * A version key that will never clash with a real Uplink version.
     */
    private const TEST_VERSION = '99.0.0-test';

    private SuppressLegacyLicenses $action;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->action = new SuppressLegacyLicenses(new LicenseRepository());

        // Register stub callbacks for our test version so the global Uplink
        // functions resolve to whatever we set via setUplinkActive().
        $this->setUplinkActive(false);

        // Make our test version appear to be the highest so the global
        // function wrappers pick up our callbacks.
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
     * Returns our test version as the "highest" Uplink version so the global
     * function wrappers resolve to our registered callbacks.
     *
     * @unreleased
     */
    public function filterHighestVersion(): string
    {
        return self::TEST_VERSION;
    }

    /**
     * Registers stub callbacks into the Uplink global function registry for
     * our pinned test version.
     *
     * @unreleased
     */
    private function setUplinkActive(bool $active): void
    {
        // _stellarwp_uplink_global_function_registry() is a plain PHP function
        // defined in vendor/vendor-prefixed/stellarwp/uplink/src/Uplink/global-functions.php.
        // Passing a $callback stores it; omitting $callback looks up the stored one.
        _stellarwp_uplink_global_function_registry(
            'stellarwp_uplink_has_unified_license_key',
            self::TEST_VERSION,
            static function () use ($active): bool {
                return $active;
            }
        );

        _stellarwp_uplink_global_function_registry(
            'stellarwp_uplink_is_product_license_active',
            self::TEST_VERSION,
            static function (string $product) use ($active): bool {
                return $active && $product === 'give';
            }
        );
    }

    /**
     * Registers all legacy hooks so we can assert whether they were removed.
     *
     * @unreleased
     */
    private function addLegacyHooks(): void
    {
        add_action('admin_notices', 'give_license_notices', 10);
        add_filter('pre_set_site_transient_update_plugins', 'give_check_addon_updates', 999);
        add_action('give_thricely_scheduled_events', 'give_refresh_licenses', 10);
    }

    /**
     * New Uplink customer: no legacy DB licenses, Uplink is active.
     * All legacy hooks should be removed.
     *
     * @unreleased
     */
    public function testAllHooksRemovedWhenUplinkActiveAndNoLegacyLicenses(): void
    {
        $this->setUplinkActive(true);
        $this->addLegacyHooks();

        ($this->action)();

        $this->assertFalse(
            has_action('admin_notices', 'give_license_notices'),
            'Admin notice hook should be removed'
        );
        $this->assertFalse(
            has_filter('pre_set_site_transient_update_plugins', 'give_check_addon_updates'),
            'Update check filter should be removed'
        );
        $this->assertFalse(
            has_action('give_thricely_scheduled_events', 'give_refresh_licenses'),
            'Cron action should be removed'
        );
    }

    /**
     * Transitioning customer: legacy licenses exist in DB, Uplink is active.
     * Only the admin notice hook should be removed; update checks and cron must remain.
     *
     * @unreleased
     */
    public function testOnlyNoticeRemovedWhenUplinkActiveAndLegacyLicensesExist(): void
    {
        $this->setUplinkActive(true);
        $this->addLegacyHooks();

        update_option(LicenseOptionKeys::LICENSES, [
            'license-key-1234567890' => $this->getRawLicenseData(),
        ]);

        ($this->action)();

        $this->assertFalse(
            has_action('admin_notices', 'give_license_notices'),
            'Admin notice hook should be removed even when legacy licenses exist'
        );
        $this->assertNotFalse(
            has_filter('pre_set_site_transient_update_plugins', 'give_check_addon_updates'),
            'Update check filter should remain when legacy licenses exist'
        );
        $this->assertNotFalse(
            has_action('give_thricely_scheduled_events', 'give_refresh_licenses'),
            'Cron action should remain when legacy licenses exist'
        );
    }

    /**
     * Pure legacy customer: Uplink is not active.
     * No hooks should be removed.
     *
     * @unreleased
     */
    public function testNothingRemovedWhenUplinkNotActive(): void
    {
        $this->setUplinkActive(false);
        $this->addLegacyHooks();

        ($this->action)();

        $this->assertNotFalse(
            has_action('admin_notices', 'give_license_notices'),
            'Admin notice hook should remain when Uplink is not active'
        );
        $this->assertNotFalse(
            has_filter('pre_set_site_transient_update_plugins', 'give_check_addon_updates'),
            'Update check filter should remain when Uplink is not active'
        );
        $this->assertNotFalse(
            has_action('give_thricely_scheduled_events', 'give_refresh_licenses'),
            'Cron action should remain when Uplink is not active'
        );
    }

    /**
     * Uplink has a unified key but Give's license is specifically not active.
     * No hooks should be removed.
     *
     * @unreleased
     */
    public function testNothingRemovedWhenUplinkKeyExistsButGiveLicenseNotActive(): void
    {
        // Unified key exists but Give's license is not active
        _stellarwp_uplink_global_function_registry(
            'stellarwp_uplink_has_unified_license_key',
            self::TEST_VERSION,
            static function (): bool {
                return true;
            }
        );
        _stellarwp_uplink_global_function_registry(
            'stellarwp_uplink_is_product_license_active',
            self::TEST_VERSION,
            static function (): bool {
                return false;
            }
        );

        $this->addLegacyHooks();

        ($this->action)();

        $this->assertNotFalse(
            has_action('admin_notices', 'give_license_notices'),
            'Admin notice hook should remain when Give license is not active'
        );
        $this->assertNotFalse(
            has_filter('pre_set_site_transient_update_plugins', 'give_check_addon_updates'),
            'Update check filter should remain when Give license is not active'
        );
        $this->assertNotFalse(
            has_action('give_thricely_scheduled_events', 'give_refresh_licenses'),
            'Cron action should remain when Give license is not active'
        );
    }
}
