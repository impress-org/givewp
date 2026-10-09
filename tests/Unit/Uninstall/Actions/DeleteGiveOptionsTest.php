<?php

namespace Give\Tests\Unit\Uninstall\Actions;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Uninstall\Actions\DeleteGiveOptions;

/**
 * Covers the option cleanup of the uninstall script.
 *
 * @since TBD
 */
class DeleteGiveOptionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    private function giveOptionNames(): array
    {
        global $wpdb;

        return [
            'give_test_uninstall_settings',
            'give_cache_test_uninstall',
            'givewp_test_uninstall_flag',
            '_give_test_uninstall',
            'widget_give_test_uninstall_widget',
            '_transient_give_test_uninstall',
            '_transient_timeout_give_test_uninstall',
            $wpdb->prefix . 'give_test_uninstall_db_version',
        ];
    }

    /**
     * Options of other plugins. Several have "give" in the name, one is the name from issue #8323.
     *
     * @since TBD
     */
    private function otherOptionNames(): array
    {
        return [
            'givebutter-widget-account',
            'giveaway_test_uninstall_option',
            'my_give_test_uninstall_option',
            'forgive_test_uninstall_option',
            'other_plugin_test_uninstall_setting',
            'widget_giver_test_uninstall',
            '_transient_giveaway_test_uninstall',
        ];
    }

    /**
     * @since TBD
     */
    public function testItDeletesTheOptionsOfGiveWPAndLeavesTheOptionsOfOtherPlugins()
    {
        foreach (array_merge($this->giveOptionNames(), $this->otherOptionNames()) as $name) {
            update_option($name, 'value', false);
        }

        $deleted = (new DeleteGiveOptions())();

        foreach ($this->giveOptionNames() as $name) {
            $this->assertFalse(get_option($name, false), "$name should be deleted");
            $this->assertContains($name, $deleted);
        }

        foreach ($this->otherOptionNames() as $name) {
            $this->assertSame('value', get_option($name), "$name must not be deleted");
            $this->assertNotContains($name, $deleted);
        }
    }

    /**
     * @since TBD
     */
    public function testItDeletesTheGiveSettingsOption()
    {
        update_option('give_settings', ['uninstall_on_delete' => 'enabled']);

        (new DeleteGiveOptions())();

        $this->assertFalse(get_option('give_settings', false));
    }

    /**
     * @since TBD
     */
    public function testUnderscoreInThePrefixIsNotAWildcard()
    {
        // "give_" has an underscore. If it were a LIKE wildcard, "giveXtest" would match too.
        update_option('giveXtest_uninstall', 'value', false);
        update_option('givewpXtest_uninstall', 'value', false);

        (new DeleteGiveOptions())();

        $this->assertSame('value', get_option('giveXtest_uninstall'));
        $this->assertSame('value', get_option('givewpXtest_uninstall'));
    }

    /**
     * @since TBD
     */
    public function testItReturnsAnEmptyListWhenThereIsNothingToDelete()
    {
        (new DeleteGiveOptions())();

        $this->assertSame([], (new DeleteGiveOptions())());
    }

    /**
     * @since TBD
     */
    public function testTheLikePatternsStartWithAGiveWPPrefix()
    {
        foreach ((new DeleteGiveOptions())->getLikePatterns() as $pattern) {
            $this->assertStringEndsWith('%', $pattern);
            $this->assertStringNotContainsString('%give%', $pattern, 'No pattern may match "give" anywhere in the name');
            $this->assertStringStartsNotWith('%', $pattern);
        }
    }
}
