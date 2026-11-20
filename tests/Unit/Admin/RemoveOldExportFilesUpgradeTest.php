<?php

namespace Give\Tests\Unit\Admin;

use Give\Tests\TestCase;

/**
 * @since TBD
 */
final class RemoveOldExportFilesUpgradeTest extends TestCase
{
    private const FILE_NAMES = ['give-payments.csv', 'give-donors.csv'];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';

        wp_mkdir_p(WP_CONTENT_DIR . '/uploads');
    }

    /**
     * @since TBD
     */
    public function testRemovesBothOldExportFiles(): void
    {
        foreach (self::FILE_NAMES as $fileName) {
            file_put_contents(WP_CONTENT_DIR . '/uploads/' . $fileName, 'old export');
        }

        give_v290_remove_old_export_files();

        foreach (self::FILE_NAMES as $fileName) {
            $this->assertFalse(file_exists(WP_CONTENT_DIR . '/uploads/' . $fileName));
        }
    }

    /**
     * @since TBD
     */
    public function testMissingFilesRaiseNoWarning(): void
    {
        give_v290_remove_old_export_files();
        give_v290_remove_old_export_files();

        foreach (self::FILE_NAMES as $fileName) {
            $this->assertFalse(file_exists(WP_CONTENT_DIR . '/uploads/' . $fileName));
        }
    }
}
