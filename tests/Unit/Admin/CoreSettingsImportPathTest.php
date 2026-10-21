<?php

namespace Give\Tests\Unit\Admin;

use Give\Tests\TestCase;
use WPDieException;

/**
 * Covers the file name validation added to the core settings import.
 *
 * @since 4.19.0.1
 *
 * @covers ::give_get_core_settings_json
 * @covers ::give_core_settings_import_callback
 */
final class CoreSettingsImportPathTest extends TestCase
{
    /**
     * @var string
     */
    private $uploadDir;

    /**
     * @since 4.19.0.1
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!function_exists('give_get_core_settings_json')) {
            require_once GIVE_PLUGIN_DIR . 'includes/misc-functions.php';
        }

        if (!function_exists('give_core_settings_import_callback')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-actions.php';
        }

        if (!function_exists('give_import_page_url')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/import-functions.php';
        }

        if (!class_exists('Give_Admin_Settings')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/class-admin-settings.php';
        }

        $uploads = wp_upload_dir();
        $this->uploadDir = $uploads['path'];
        wp_mkdir_p($this->uploadDir);

        // wp_send_json_* calls wp_die() only when wp_doing_ajax() is true.
        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler'], 11);
        add_filter('wp_die_json_handler', [$this, 'get_wp_die_handler'], 11);
        add_filter('wp_die_handler', [$this, 'get_wp_die_handler'], 11);
    }

    /**
     * @since 4.19.0.1
     */
    public function tearDown(): void
    {
        $_POST = [];
        $_REQUEST = [];

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler'], 11);
        remove_filter('wp_die_json_handler', [$this, 'get_wp_die_handler'], 11);
        remove_filter('wp_die_handler', [$this, 'get_wp_die_handler'], 11);

        parent::tearDown();
    }

    /**
     * A bare file name inside the uploads directory is still readable.
     *
     * @since 4.19.0.1
     */
    public function testReadsFileInsideUploads(): void
    {
        $fileName = 'svul157-valid-settings.json';
        $contents = '{"currency":"USD"}';
        file_put_contents($this->uploadDir . '/' . $fileName, $contents);

        try {
            $this->assertSame($contents, give_get_core_settings_json($fileName));
        } finally {
            @unlink($this->uploadDir . '/' . $fileName);
        }
    }

    /**
     * A file name containing a path separator is refused.
     *
     * @since 4.19.0.1
     */
    public function testRejectsFileNameWithTraversal(): void
    {
        $fileName = 'svul157-outside-settings.json';
        file_put_contents(WP_CONTENT_DIR . '/' . $fileName, '{"secret":true}');

        try {
            $this->assertFalse(give_get_core_settings_json('../' . $fileName));
            $this->assertFalse(give_get_core_settings_json('../../' . $fileName));
        } finally {
            @unlink(WP_CONTENT_DIR . '/' . $fileName);
        }
    }

    /**
     * An empty or non-string file name is refused.
     *
     * @since 4.19.0.1
     */
    public function testRejectsEmptyFileName(): void
    {
        $this->assertFalse(give_get_core_settings_json(''));
        $this->assertFalse(give_get_core_settings_json(null));
    }

    /**
     * Importing a file that does not decode to an array leaves the stored settings untouched.
     *
     * @since 4.19.0.1
     */
    public function testNonJsonImportDoesNotReplaceSettings(): void
    {
        $fileName = 'svul157-not-json.txt';
        file_put_contents($this->uploadDir . '/' . $fileName, 'this is not json');

        $settings = ['currency' => 'USD', 'base_country' => 'US'];
        update_option('give_settings', $settings);

        $user = $this->factory->user->create(['role' => 'give_manager']);
        wp_set_current_user($user);

        $_POST['_wpnonce'] = $_REQUEST['_wpnonce'] = wp_create_nonce('give_core_settings_import');
        $_POST['fields'] = 'file_name=' . $fileName . '&type=replace';

        try {
            give_core_settings_import_callback();
            $this->fail('Expected the handler to end the request.');
        } catch (WPDieException $e) {
            // Expected: wp_send_json() ends the request.
        } finally {
            @unlink($this->uploadDir . '/' . $fileName);
        }

        $this->assertSame($settings, get_option('give_settings'));
    }
}
