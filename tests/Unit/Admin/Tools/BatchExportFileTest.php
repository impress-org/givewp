<?php

namespace Give\Tests\Unit\Admin\Tools;

use Give\Tests\TestCase;
use Give_Batch_Export;
use ReflectionMethod;

/**
 * Locks the export file lifecycle of Give_Batch_Export: step 1 starts a fresh file, later steps
 * append to it, and get_file() creates the file when it is missing.
 *
 * @since TBD
 */
final class BatchExportFileTest extends TestCase
{
    private const FILE_NAME = 'give-batch-export-file-test';

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export.php';

        add_filter('give_export_capability', '__return_true');
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        wp_delete_file($this->filePath());
        remove_filter('give_export_capability', '__return_true');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testStepOneReplacesAnOldFile(): void
    {
        file_put_contents($this->filePath(), "stale content\r\n");

        $export = $this->makeExport(1);

        $this->assertTrue($export->is_writable);
        $this->assertFalse($export->process_step());
        $this->assertSame("\"ID\",\"Name\"\r\n\"1\",\"Ann\"\r\n", file_get_contents($this->filePath()));
    }

    /**
     * @since TBD
     */
    public function testLaterStepAppendsToTheFile(): void
    {
        $this->makeExport(1)->process_step();
        $this->makeExport(2)->process_step();

        $this->assertSame(
            "\"ID\",\"Name\"\r\n\"1\",\"Ann\"\r\n\"1\",\"Ann\"\r\n",
            file_get_contents($this->filePath())
        );
    }

    /**
     * @since TBD
     */
    public function testGetFileCreatesAMissingFileAndKeepsItWritable(): void
    {
        wp_delete_file($this->filePath());

        $export = $this->makeExport(1);
        $getFile = new ReflectionMethod($export, 'get_file');
        $getFile->setAccessible(true);

        $this->assertSame('', $getFile->invoke($export));
        $this->assertFileExists($this->filePath());

        // The second call reads the existing file and checks that it is writable.
        $this->assertSame('', $getFile->invoke($export));
        $this->assertTrue($export->is_writable);
    }

    /**
     * @since TBD
     */
    private function makeExport(int $step): Give_Batch_Export
    {
        return new class($step, self::FILE_NAME) extends Give_Batch_Export {
            public $export_type = 'give_batch_export_file_test';

            public function csv_cols()
            {
                return ['id' => 'ID', 'name' => 'Name'];
            }

            public function get_data()
            {
                return [['id' => 1, 'name' => 'Ann']];
            }
        };
    }

    /**
     * @since TBD
     */
    private function filePath(): string
    {
        return trailingslashit(wp_upload_dir()['basedir']) . self::FILE_NAME . '.csv';
    }
}
