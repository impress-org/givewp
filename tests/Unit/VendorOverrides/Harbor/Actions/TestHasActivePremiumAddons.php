<?php

declare(strict_types=1);

namespace Give\Tests\Unit\VendorOverrides\Harbor\Actions;

use Give\License\PremiumAddonsListManager;
use Give\Tests\TestCase;
use Give\VendorOverrides\Harbor\Actions\HasActivePremiumAddons;

/**
 * @since 4.15.2
 * @coversDefaultClass \Give\VendorOverrides\Harbor\Actions\HasActivePremiumAddons
 */
class TestHasActivePremiumAddons extends TestCase
{
    private const ADDON_SLUG = 'give-harbor-test-add-on';

    private const PREMIUM_ADDONS_TRANSIENT = 'give_premium_addons_ids';

    private HasActivePremiumAddons $action;

    private array $activePlugins = [];

    /**
     * @since 4.15.2
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->requirePluginApi();
        $this->activePlugins = (array) get_option('active_plugins', []);
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
        update_option('active_plugins', [$this->pluginFile()]);

        $this->assertTrue(($this->action)(false));
    }

    /**
     * @since 4.15.2
     */
    private function installPremiumAddonFixture(): void
    {
        $this->registerPremiumSlugs([self::ADDON_SLUG]);
        $this->registerPluginInCache();
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
     * Puts the add-on in the plugins cache that get_plugins() reads, instead of writing a plugin file.
     * Every paratest worker shares the plugins directory, so a file here could vanish while another
     * worker is scanning it.
     *
     * @since TBD
     */
    private function registerPluginInCache(): void
    {
        wp_cache_set('plugins', ['' => [
            $this->pluginFile() => [
                'Name'        => 'Give Harbor Test Add-on',
                'PluginURI'   => 'https://givewp.com/downloads/plugins/give-harbor-test-add-on',
                'Version'     => '1.0.0',
                'Description' => 'Test fixture for Harbor premium add-on detection.',
                'Author'      => 'GiveWP',
                'AuthorURI'   => '',
                'TextDomain'  => '',
                'DomainPath'  => '',
                'Network'     => false,
                'RequiresWP'  => '',
                'RequiresPHP' => '',
                'UpdateURI'   => '',
                'RequiresPlugins' => '',
                'Title'       => 'Give Harbor Test Add-on',
                'AuthorName'  => 'GiveWP',
            ],
        ]], 'plugins');
    }

    /**
     * Returns every piece of touched state to its baseline. Idempotent: safe to call
     * from setUp before anything has been installed, and from tearDown after a test
     * has fully or partially run.
     *
     * @since TBD Reset the cached plugin list and the active plugins, not a plugin file.
     * @since 4.15.2
     */
    private function resetState(): void
    {
        update_option('active_plugins', $this->activePlugins);

        give()->instance(PremiumAddonsListManager::class, new PremiumAddonsListManager());
        delete_transient(self::PREMIUM_ADDONS_TRANSIENT);
        wp_clean_plugins_cache(true);
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
    private function pluginFile(): string
    {
        return self::ADDON_SLUG . '/' . self::ADDON_SLUG . '.php';
    }
}
