<?php

namespace Give\Tests\Unit\Admin\Tools;

use Give\Tests\TestCase;
use Give_Tools_Recount_Income;
use ReflectionMethod;

/**
 * @since TBD
 */
final class BatchToolOptionsTest extends TestCase
{
    private const KEY = 'give_temp_batch_tool_options_test';

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-income.php';
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        delete_option(self::KEY);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testArrayIsStoredAsJsonWithoutAutoloadAndReadBack(): void
    {
        $tool = new Give_Tools_Recount_Income();
        $value = ['a' => 1, 'b' => ['it\'s "quoted" \\ slash']];

        $this->call($tool, 'store_data', self::KEY, $value);

        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT option_value, autoload FROM $wpdb->options WHERE option_name = %s", self::KEY)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- the test must read the raw row to check the stored value and autoload flag.

        $this->assertSame(wp_json_encode($value), $row->option_value);
        $this->assertContains($row->autoload, ['no', 'off']);
        $this->assertSame($value, $this->call($tool, 'get_stored_data', self::KEY));
    }

    /**
     * @since TBD
     */
    public function testNumericStringIsDecodedLikeBefore(): void
    {
        $tool = new Give_Tools_Recount_Income();

        $this->call($tool, 'store_data', self::KEY, '12.5');

        // The stored string is "12.5"; json_decode() turns it into a float, as it did before.
        $this->assertSame(12.5, $this->call($tool, 'get_stored_data', self::KEY));
    }

    /**
     * @since TBD
     */
    public function testMissingAndDeletedOptionsReturnFalse(): void
    {
        $tool = new Give_Tools_Recount_Income();

        $this->assertFalse($this->call($tool, 'get_stored_data', self::KEY));

        $this->call($tool, 'store_data', self::KEY, ['x' => 1]);
        $this->call($tool, 'delete_data', self::KEY);

        $this->assertFalse($this->call($tool, 'get_stored_data', self::KEY));
        $this->assertFalse(get_option(self::KEY, false));
    }

    /**
     * @since TBD
     */
    private function call(object $object, string $method, ...$args)
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($object, ...$args);
    }
}
