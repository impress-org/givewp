<?php

use PHPUnit\Framework\TestCase;

require_once GIVE_PLUGIN_DIR . 'includes/class-give-readme-parser.php';

/**
 * @since 2.20.2
 */
class Tests_Give_Readme_Parser extends TestCase
{
    /**
     * @since 2.20.2
     */
    public function test_should_return_requires_give()
    {
        $this->assertEquals('2.25.0', (new Mock_Give_Readme_Parser('fake_url_value'))->requires_at_least());
    }

    /**
     * @since 2.20.2
     */
    public function test_should_return_requires_givewp()
    {
        $this->assertEquals('2.26.0', (new Mock_GiveWP_Readme_Parser('fake_url_value'))->requires_at_least());
    }
}

/**
 * @since 2.20.2
 */
class Mock_Give_Readme_Parser extends Give_Readme_Parser
{
    /**
     * @since 2.20.2
     */
    protected function get_readme_file_content(): string
    {
        return '=== Give - Addon ===
Requires at least: 5.0
Requires PHP: 7.0
Requires Give: 2.25.0

Add-on for Give.';
    }
}

/**
 * @since 2.20.2
 */
class Mock_GiveWP_Readme_Parser extends Give_Readme_Parser
{
    /**
     * @since 2.20.2
     */
    protected function get_readme_file_content(): string
    {
        return '=== Give - Addon ===
Requires at least: 5.0
Requires PHP: 7.0
Requires GiveWP: 2.26.0

Add-on for GiveWP.';
    }
}
