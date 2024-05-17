<?php

namespace Give\Tests\Unit;

use Give\Tests\TestCase;

/**
 * @since 3.10.0
 */
class TestGive extends TestCase
{
    /**
     * @since 3.10.0
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
     * @since 3.10.0
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
     * @since 3.10.0
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
