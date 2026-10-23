<?php

namespace Give\Tests\Unit\LegacyTools\Data;

use Give\Tests\TestCase;
use Give_Tools_Reset_Stats;

/**
 * Covers how Reset Stats clears the object cache after its raw option delete.
 *
 * @since TBD
 *
 * @covers \Give_Tools_Reset_Stats::get_data
 */
final class TestResetStatsCache extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        require_once \GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php';
        require_once \GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export.php';
        require_once \GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-reset-stats.php';

        parent::setUp();
    }

    /**
     * @since TBD
     */
    public function testClearsDeletedOptionsFromTheCacheAndKeepsTheRestOfTheCache(): void
    {
        $postId = wp_insert_post(['post_type' => 'give_payment', 'post_status' => 'publish', 'post_title' => 'Donation']);
        update_option('give_reset_stats_test_option', 'value');
        update_option('give_temp_reset_ids', wp_json_encode([['id' => $postId, 'type' => 'give_payment']]), false);
        wp_cache_set('unrelated_key', 'kept', 'unrelated_group');

        $this->assertSame('value', get_option('give_reset_stats_test_option'));

        (new Give_Tools_Reset_Stats(1))->get_data();

        $this->assertFalse(get_option('give_reset_stats_test_option'));
        $this->assertSame('kept', wp_cache_get('unrelated_key', 'unrelated_group'));
    }
}
