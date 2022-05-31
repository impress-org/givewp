<?php

use PHPUnit\Framework\TestCase;

require_once GIVE_PLUGIN_DIR . 'includes/class-give-readme-parser.php';

/**
 * @unreleased
 */
class Tests_Give_Readme_Parser extends TestCase
{
    /**
     * @unreleased
     */
    public function test_should_return_requires_give()
    {
        $this->assertEquals('2.25.0', (new Mock_Give_Readme_Parser('fake_url_value'))->requires_at_least());
    }

    /**
     * @unreleased
     */
    public function test_should_return_requires_givewp()
    {
        $this->assertEquals('2.26.0', (new Mock_GiveWP_Readme_Parser('fake_url_value'))->requires_at_least());
    }
}

/**
 * @unreleased
 */
class Mock_Give_Readme_Parser extends Give_Readme_Parser
{
    /**
     * @unreleased
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
 * @unreleased
 */
class Mock_GiveWP_Readme_Parser extends Give_Readme_Parser
{
    /**
     * @unreleased
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
