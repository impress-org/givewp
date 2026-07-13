<?php

namespace Give\Tests\Unit\Admin;

use WP_Ajax_UnitTestCase;
use WP_User;

/**
 * Covers nonce validation in the give_core_settings_import AJAX handler.
 *
 * @since 4.16.4
 *
 * @covers ::give_core_settings_import_callback
 */
final class CoreSettingsImportNonceTest extends WP_Ajax_UnitTestCase
{
    /**
     * Guarantee the handler is registered even when the admin bootstrap did not
     * run in the test environment.
     *
     * @since 4.16.4
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!function_exists('give_core_settings_import_callback')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-actions.php';
        }
    }

    /**
     * Verifies that requests without a valid nonce are rejected before any option write.
     *
     * @since 4.16.4
     */
    public function testRejectsRequestWithoutValidNonce(): void
    {
        $this->loginAsGiveAdmin();

        unset(
            $_REQUEST['_wpnonce'],
            $_POST['_wpnonce'],
            $_REQUEST['_ajax_nonce'],
            $_POST['_ajax_nonce']
        );
        $_POST['fields'] = 'file_name=nonexistent.json&type=replace';

        $settingsBefore = get_option('give_settings');

        try {
            $this->_handleAjax('give_core_settings_import');
            $this->fail('Expected the handler to reject the request without a valid nonce.');
        } catch (\WPAjaxDieStopException $e) {
            $this->assertSame('-1', $e->getMessage());
        }

        $this->assertSame(
            $settingsBefore,
            get_option('give_settings'),
            'A request without a valid nonce must not modify give_settings.'
        );
    }

    /**
     * Verifies that a request with a valid nonce is still processed.
     *
     * @since 4.16.4
     */
    public function testAcceptsRequestWithValidNonce(): void
    {
        $this->loginAsGiveAdmin();

        $_POST['_wpnonce'] = wp_create_nonce('give_core_settings_import');
        /* Empty file_name => handler skips update_option and returns success:false. */
        $_POST['fields'] = 'file_name=&type=merge';

        try {
            $this->_handleAjax('give_core_settings_import');
        } catch (\WPAjaxDieContinueException $e) {
            /* wp_send_json() terminates the request via this exception. */
        }

        $response = json_decode($this->_last_response, true);

        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertSame(100, $response['percentage']);
    }

    /**
     * Logs in as an administrator carrying the give_core_settings capability,
     * independent of whether the role was seeded with GiveWP caps in this env.
     *
     * @since 4.16.4
     */
    private function loginAsGiveAdmin(): void
    {
        $adminId = $this->factory()->user->create(['role' => 'administrator']);
        $user = new WP_User($adminId);
        $user->add_cap('manage_give_settings');
        wp_set_current_user($adminId);
    }
}
