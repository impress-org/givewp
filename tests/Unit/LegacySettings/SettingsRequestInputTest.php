<?php

namespace Give\Tests\Unit\LegacySettings;

use Give\Tests\TestCase;
use Give_Admin_Settings;

/**
 * @since 4.19.1
 */
final class SettingsRequestInputTest extends TestCase
{
    /**
     * @since 4.19.1
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/admin/setting-page-functions.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/ajax-handler.php';
    }

    /**
     * @since 4.19.1
     */
    public function tearDown(): void
    {
        unset($_GET['tab'], $_GET['page'], $_REQUEST['section'], $_REQUEST['_give-save-settings']);

        parent::tearDown();
    }

    /**
     * @since 4.19.1
     */
    public function testCurrentTabIsUnslashedAndSanitized(): void
    {
        $_GET['tab'] = 'gate\\ways<b>';

        $this->assertSame('gateways', give_get_current_setting_tab());
    }

    /**
     * @since 4.19.1
     */
    public function testCurrentSectionAndPageAreUnslashedAndSanitized(): void
    {
        $_REQUEST['section'] = 'paypal\\-standard';
        $_GET['page'] = 'give-settings<script>';

        $this->assertSame('paypal-standard', give_get_current_setting_section());
        $this->assertSame('give-settings', give_get_current_setting_page());
    }

    /**
     * @since 4.19.1
     */
    public function testVerifyNonceAcceptsAValidSlashedNonce(): void
    {
        $nonce = wp_create_nonce('give-save-settings');

        $_REQUEST['_give-save-settings'] = addslashes($nonce);

        $this->assertTrue(Give_Admin_Settings::verify_nonce());
        $this->assertTrue(give_is_saving_settings());
    }

    /**
     * @since 4.19.1
     */
    public function testVerifyNonceRejectsABadNonce(): void
    {
        $_REQUEST['_give-save-settings'] = 'bad-nonce';

        $this->assertFalse(Give_Admin_Settings::verify_nonce());
        $this->assertFalse(give_is_saving_settings());
    }
}
