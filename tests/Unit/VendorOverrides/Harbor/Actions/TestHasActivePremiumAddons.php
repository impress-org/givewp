<?php

declare(strict_types=1);

namespace Give\Tests\Unit\VendorOverrides\Harbor\Actions;

use FilesystemIterator;
use Give\License\PremiumAddonsListManager;
use Give\Tests\TestCase;
use Give\VendorOverrides\Harbor\Actions\HasActivePremiumAddons;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @since 4.15.2
 * @coversDefaultClass \Give\VendorOverrides\Harbor\Actions\HasActivePremiumAddons
 */
class TestHasActivePremiumAddons extends TestCase
{
    private const ADDON_SLUG = 'give-harbor-test-add-on';

    private const PREMIUM_ADDONS_TRANSIENT = 'give_premium_addons_ids';

    private HasActivePremiumAddons $action;

    /**
     * @since 4.15.2
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->requirePluginApi();
        $this->resetState();

        $this->action = new HasActivePremiumAddons();
    }

    /**
     * @since 4.15.2
     */
    public function tearDown(): void
    {
        $this->resetState();

        parent::tearDown();
    }

    /**
     * @since 4.15.2
     */
    public function testReturnsTrueWhenOtherBrandsAlreadyHavePremiumPresence(): void
    {
        $this->assertTrue(($this->action)(true));
    }

    /**
     * @since 4.15.2
     */
    public function testReturnsFalseWhenNoPremiumAddOnsMatch(): void
    {
        $this->registerPremiumSlugs([]);

        $this->assertFalse(($this->action)(false));
    }

    /**
     * @since 4.15.2
     */
    public function testReturnsFalseWhenPremiumAddOnExistsButIsInactive(): void
    {
        $this->installPremiumAddonFixture();

        $this->assertFalse(($this->action)(false));
    }

    /**
     * @since 4.15.2
     */
    public function testReturnsTrueWhenPremiumAddOnIsActive(): void
    {
        $this->installPremiumAddonFixture();
        activate_plugin($this->pluginFile(), '', false, true);

        $this->assertTrue(($this->action)(false));
    }

    /**
     * @since 4.15.2
     */
    private function installPremiumAddonFixture(): void
    {
        $this->registerPremiumSlugs([self::ADDON_SLUG]);
        $this->writePluginFixture();
        wp_clean_plugins_cache(true);
    }

    /**
     * Bypasses the remote products API by seeding the cached premium add-on slug list directly.
     * The container rebind clears the per-request memoized list inside PremiumAddonsListManager.
     *
     * @since 4.15.2
     */
    private function registerPremiumSlugs(array $slugs): void
    {
        give()->instance(PremiumAddonsListManager::class, new PremiumAddonsListManager());
        set_transient(self::PREMIUM_ADDONS_TRANSIENT, $slugs, HOUR_IN_SECONDS);
    }

    /**
     * @since 4.15.2
     */
    private function writePluginFixture(): void
    {
        $dir = $this->pluginDir();

        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            self::fail("Could not create test plugin directory: {$dir}");
        }

        $pluginPhp = <<<'PHP'
<?php
/**
 * Plugin Name: Give Harbor Test Add-on
 * Plugin URI: https://givewp.com/downloads/plugins/give-harbor-test-add-on
 * Description: Test fixture for Harbor premium add-on detection.
 * Version: 1.0.0
 * Author: GiveWP
 */
PHP;

        $bytes = file_put_contents($dir . '/' . self::ADDON_SLUG . '.php', $pluginPhp);
        self::assertNotFalse($bytes, 'Failed to write test plugin fixture.');
    }

    /**
     * Returns every piece of touched state to its baseline. Idempotent: safe to call
     * from setUp before anything has been installed, and from tearDown after a test
     * has fully or partially run.
     *
     * @since 4.15.2
     */
    private function resetState(): void
    {
        if (function_exists('is_plugin_active') && is_plugin_active($this->pluginFile())) {
            deactivate_plugins($this->pluginFile(), true);
        }

        $this->removePluginFixture();

        give()->instance(PremiumAddonsListManager::class, new PremiumAddonsListManager());
        delete_transient(self::PREMIUM_ADDONS_TRANSIENT);
        wp_clean_plugins_cache(true);
    }

    /**
     * @since 4.15.2
     */
    private function removePluginFixture(): void
    {
        $dir = $this->pluginDir();

        if (!is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $path = $item->getPathname();
            $removed = $item->isDir() ? rmdir($path) : unlink($path);
            self::assertTrue($removed, "Failed to remove test fixture entry: {$path}");
        }

        self::assertTrue(rmdir($dir), "Failed to remove test plugin directory: {$dir}");
    }

    /**
     * @since 4.15.2
     */
    private function requirePluginApi(): void
    {
        if (!function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
    }

    /**
     * @since 4.15.2
     */
    private function pluginDir(): string
    {
        return WP_PLUGIN_DIR . '/' . self::ADDON_SLUG;
    }

    /**
     * @since 4.15.2
     */
    private function pluginFile(): string
    {
        return self::ADDON_SLUG . '/' . self::ADDON_SLUG . '.php';
    }
}
