<?php

namespace Give\Tests\Unit\LegacyCache;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Cache;
use Give_Cache_Setting;

/**
 * Covers the SQL queries of Give_Cache and Give_Cache_Setting.
 *
 * @since TBD
 */
class GiveCacheQueriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testGetOptionsLikeWithFieldsReturnsNamesAndUnserializedValues(): void
    {
        Give_Cache::set('get_reports', [1647, 1550], HOUR_IN_SECONDS, true);
        Give_Cache::set('get_reports_count', [1, 2], HOUR_IN_SECONDS, true);
        Give_Cache::set('get_logs', [3, 4], HOUR_IN_SECONDS, true);

        $options = Give_Cache::get_options_like('get_reports', true);

        $this->assertCount(2, $options);

        $names = array_column($options, 'option_name');
        sort($names);

        $this->assertSame(['give_cache_get_reports', 'give_cache_get_reports_count'], $names);

        foreach ($options as $option) {
            $this->assertIsArray($option['option_value']);
            $this->assertArrayHasKey('data', $option['option_value']);
        }
    }

    /**
     * @since TBD
     */
    public function testGetOptionsLikeWithoutFieldsReturnsOnlyNames(): void
    {
        Give_Cache::set('get_reports', [1647, 1550], HOUR_IN_SECONDS, true);
        Give_Cache::set('get_logs', [3, 4], HOUR_IN_SECONDS, true);

        $this->assertSame(['give_cache_get_reports'], Give_Cache::get_options_like('get_reports'));
    }

    /**
     * @since TBD
     */
    public function testDeleteAllExpiredWithForceRemovesEveryCacheOption(): void
    {
        Give_Cache::set('get_reports', [1647, 1550], HOUR_IN_SECONDS, true);
        Give_Cache::set('get_logs', [3, 4], HOUR_IN_SECONDS, true);

        Give_Cache::delete_all_expired(true);

        $this->assertSame([], Give_Cache::get_options_like('get_', false));
    }

    /**
     * @since TBD
     */
    public function testSettingsAreLoadedFromTheDatabaseForTheListedOptionIds(): void
    {
        update_option('give_version', '9.9.9');
        update_option('give_unlisted_option_for_test', 'ignored');

        wp_cache_flush();
        Give_Cache_Setting::get_instance()->reload_plugin_settings('give_version');

        $this->assertSame('9.9.9', Give_Cache_Setting::get_option('give_version'));
        $this->assertArrayNotHasKey('give_unlisted_option_for_test', Give_Cache_Setting::get_settings());
    }
}
