<?php

namespace Give\Tests\Unit\Admin;

use Give\Tests\TestCase;
use Give_Core_Settings_Export;
use Give_Updates;
use ReflectionMethod;

/**
 * @since 4.19.1
 */
class TestExportAndUpgradeNonces extends TestCase
{
    /**
     * @since 4.19.1
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-core-settings-export.php';

        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));
    }

    /**
     * @since 4.19.1
     */
    public function tearDown(): void
    {
        unset($_POST['give-nonce'], $_GET['_wpnonce'], $_GET['give-pause-db-upgrades'], $_GET['page']);

        parent::tearDown();
    }

    /**
     * @since 4.19.1
     */
    public function testCoreSettingsExportIsDeniedWithoutNonce()
    {
        $this->assertFalse((bool)(new Give_Core_Settings_Export())->can_export());
    }

    /**
     * @since 4.19.1
     */
    public function testCoreSettingsExportIsDeniedWithInvalidNonce()
    {
        $_POST['give-nonce'] = 'invalid';

        $this->assertFalse((bool)(new Give_Core_Settings_Export())->can_export());
    }

    /**
     * @since 4.19.1
     */
    public function testCoreSettingsExportIsAllowedWithValidNonce()
    {
        $_POST['give-nonce'] = wp_create_nonce('give_core_settings_export');

        $this->assertTrue((bool)(new Give_Core_Settings_Export())->can_export());
    }

    /**
     * @since 4.19.1
     */
    public function testUpgradeRequestWithoutNonceIsNotVerified()
    {
        $_GET['give-pause-db-upgrades'] = '1';

        $this->assertFalse($this->isVerifiedUpgradeRequest('give-pause-db-upgrades', 'give_pause_db_upgrades'));
    }

    /**
     * @since 4.19.1
     */
    public function testUpgradeRequestWithNonceForAnotherActionIsNotVerified()
    {
        $_GET['give-pause-db-upgrades'] = '1';
        $_GET['_wpnonce'] = wp_create_nonce('give_restart_db_upgrades');

        $this->assertFalse($this->isVerifiedUpgradeRequest('give-pause-db-upgrades', 'give_pause_db_upgrades'));
    }

    /**
     * @since 4.19.1
     */
    public function testUpgradeRequestWithValidNonceIsVerified()
    {
        $_GET['give-pause-db-upgrades'] = '1';
        $_GET['_wpnonce'] = wp_create_nonce('give_pause_db_upgrades');

        $this->assertTrue($this->isVerifiedUpgradeRequest('give-pause-db-upgrades', 'give_pause_db_upgrades'));
    }

    /**
     * @since 4.19.1
     */
    public function testUpgradeRequestFromUserWithoutCapabilityIsNotVerified()
    {
        wp_set_current_user($this->factory()->user->create(['role' => 'subscriber']));
        $_GET['give-pause-db-upgrades'] = '1';
        $_GET['_wpnonce'] = wp_create_nonce('give_pause_db_upgrades');

        $this->assertFalse($this->isVerifiedUpgradeRequest('give-pause-db-upgrades', 'give_pause_db_upgrades'));
    }

    /**
     * @since 4.19.1
     */
    public function testPauseLinkWithoutNonceDoesNotPauseTheUpdater()
    {
        $_GET['page'] = 'give-updates';
        $_GET['give-pause-db-upgrades'] = '1';

        $this->assertFalse(Give_Updates::get_instance()->pause_db_update());
    }

    /**
     * @since 4.19.1
     */
    private function isVerifiedUpgradeRequest(string $queryArg, string $nonceAction): bool
    {
        $method = new ReflectionMethod(Give_Updates::class, 'is_verified_upgrade_request');
        $method->setAccessible(true);

        return $method->invoke(Give_Updates::get_instance(), $queryArg, $nonceAction);
    }
}
