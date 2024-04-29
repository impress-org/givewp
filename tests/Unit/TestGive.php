<?php

namespace Give\Tests\Unit;

use Give\Tests\TestCase;

/**
 * @unreleased
 */
class TestGive extends TestCase
{
    /**
     * @unreleased
     */
    public function testReadMeVersionMatchesPluginVersion(): void
    {
        $readme = get_file_data(
            trailingslashit(GIVE_PLUGIN_DIR) . "readme.txt",
            [
                "Version" => "Stable tag"
            ]
        );

        $plugin = get_plugin_data(GIVE_PLUGIN_FILE);

        $this->assertEquals(GIVE_VERSION, $readme['Version']);
        $this->assertEquals(GIVE_VERSION, $plugin['Version']);
        $this->assertEquals($readme['Version'], $plugin['Version']);
    }

    /**
     * @unreleased
     */
    public function testReadMeRequiresPHPVersionMatchesPluginVersion(): void
    {
        $readme = get_file_data(
            trailingslashit(GIVE_PLUGIN_DIR) . "readme.txt",
            [
                "RequiresPHP" => "Requires PHP"
            ]
        );

        $plugin = get_plugin_data(GIVE_PLUGIN_FILE);

        $this->assertEquals($plugin['RequiresPHP'], $readme['RequiresPHP']);
    }

    /**
     * @unreleased
     */
    public function testReadMeRequiresWPVersionMatchesPluginHeaderVersion(): void
    {
        $readme = get_file_data(
            trailingslashit(GIVE_PLUGIN_DIR) . "readme.txt",
            [
                "RequiresWP" => "Requires at least"
            ]
        );

        $plugin = get_plugin_data(GIVE_PLUGIN_FILE);

        $this->assertEquals($plugin['RequiresWP'], $readme['RequiresWP']);
    }
}
