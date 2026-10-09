<?php

namespace Give\Tests\Unit\LegacyTools\Importer;

use Give\Tests\TestCase;
use WPDieException;

/**
 * Covers the nonce on the "Undo Importing" link of the core settings importer.
 *
 * @since TBD
 *
 * @covers \Give_Import_Core_Settings::import_success
 */
final class TestCoreSettingsImportUndo extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        require_once \GIVE_PLUGIN_DIR . 'includes/admin/setting-page-functions.php';
        require_once \GIVE_PLUGIN_DIR . 'includes/admin/tools/import/class-give-import-core-settings.php';

        parent::setUp();

        // Other tests define DOING_AJAX, which cannot be undone and sends wp_die() to the ajax die handler.
        // Route that handler to the test handler too, so wp_die() throws WPDieException in every run order.
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler']);
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($_GET['undo'], $_GET['success'], $_REQUEST['_wpnonce']);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testUndoWithoutNonceDoesNotRevertTheSettings(): void
    {
        update_option('give_settings', ['marker' => 'imported'], false);
        update_option('give_settings_old', ['marker' => 'original'], false);
        $_GET['undo'] = '1';

        try {
            ob_start();
            \Give_Import_Core_Settings::get_instance()->import_success();
            $this->fail('A request without a nonce must stop before the settings are reverted.');
        } catch (WPDieException $exception) {
            // Expected: the link is expired or forged.
        } finally {
            ob_end_clean();
        }

        $this->assertSame(['marker' => 'imported'], get_option('give_settings'));
    }

    /**
     * @since TBD
     */
    public function testUndoWithValidNonceRevertsTheSettings(): void
    {
        update_option('give_settings', ['marker' => 'imported'], false);
        update_option('give_settings_old', ['marker' => 'original'], false);
        $_GET['undo'] = '1';
        $_REQUEST['_wpnonce'] = wp_create_nonce('give_core_settings_import_undo');

        ob_start();
        \Give_Import_Core_Settings::get_instance()->import_success();
        ob_end_clean();

        $this->assertSame(['marker' => 'original'], get_option('give_settings'));
    }

    /**
     * @since TBD
     */
    public function testUndoLinkCarriesTheNonce(): void
    {
        $_GET['success'] = '1';

        ob_start();
        \Give_Import_Core_Settings::get_instance()->import_success();
        $html = ob_get_clean();

        $this->assertStringContainsString('_wpnonce=' . wp_create_nonce('give_core_settings_import_undo'), $html);
    }
}
