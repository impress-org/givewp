<?php

namespace Give\Tests\Unit\Admin;

use Exception;
use Give\Tests\TestCase;

/**
 * Covers the CSRF nonce guard added to the give_core_settings_import AJAX handler.
 *
 * The handler now calls check_ajax_referer('give_core_settings_import') before doing
 * any work. These tests drive that guard directly instead of booting the full admin
 * AJAX request lifecycle, so they neither depend on admin-only classes nor leak global
 * state (such as the DOING_AJAX constant) into other tests.
 *
 * @since 4.16.4
 *
 * @covers ::give_core_settings_import_callback
 */
final class CoreSettingsImportNonceTest extends TestCase
{
    /**
     * Message thrown from the check_ajax_referer hook to stop the handler right after
     * the nonce check, before it can write options or terminate the request.
     *
     * @since 4.16.4
     */
    private const HALT = 'give_core_settings_import_nonce_checked';

    /**
     * Guarantee the handler is loaded even when the admin bootstrap did not run in the
     * test environment.
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
     * A request without a valid nonce must fail the guard and never write options.
     *
     * @since 4.16.4
     */
    public function testRejectsRequestWithoutValidNonce(): void
    {
        unset(
            $_REQUEST['_wpnonce'],
            $_POST['_wpnonce'],
            $_REQUEST['_ajax_nonce'],
            $_POST['_ajax_nonce']
        );
        $_POST['fields'] = 'file_name=nonexistent.json&type=replace';

        $result = $this->captureNonceCheckResult();

        $this->assertFalse($result, 'An invalid nonce must fail the CSRF guard.');
    }

    /**
     * A request with a valid nonce passes the guard.
     *
     * @since 4.16.4
     */
    public function testAcceptsRequestWithValidNonce(): void
    {
        $nonce = wp_create_nonce('give_core_settings_import');
        $_REQUEST['_wpnonce'] = $_POST['_wpnonce'] = $nonce;
        $_POST['fields'] = 'file_name=&type=merge';

        $result = $this->captureNonceCheckResult();

        $this->assertNotFalse($result, 'A valid nonce must pass the CSRF guard.');
    }

    /**
     * Runs the handler but halts inside the check_ajax_referer hook, returning the
     * verification result the handler's guard received. Halting there keeps the handler
     * from writing options, sending JSON, or ending the request.
     *
     * @since 4.16.4
     *
     * @return int|false Nonce verification result (1 or 2 on success, false on failure).
     */
    private function captureNonceCheckResult()
    {
        $captured = null;

        $listener = static function ($action, $result) use (&$captured) {
            if ('give_core_settings_import' === $action) {
                $captured = $result;
                throw new Exception(self::HALT);
            }
        };

        add_action('check_ajax_referer', $listener, 10, 2);

        $settingsBefore = get_option('give_settings');

        try {
            give_core_settings_import_callback();
            $this->fail('Expected the handler to verify the nonce for the core settings import action.');
        } catch (Exception $e) {
            if (self::HALT !== $e->getMessage()) {
                throw $e;
            }
        } finally {
            remove_action('check_ajax_referer', $listener, 10);
        }

        $this->assertSame(
            $settingsBefore,
            get_option('give_settings'),
            'The handler must not modify give_settings before the nonce is verified.'
        );

        return $captured;
    }
}
