<?php

namespace Give\Tests\Unit\LegacySettings;

use Give\Tests\TestCase;
use Give_Admin_Settings;

/**
 * @since TBD
 */
final class SettingsFieldTitleTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testTitleWithTextIsReturnedAsGiven(): void
    {
        $this->assertSame('<strong>Bold</strong> title', Give_Admin_Settings::get_field_title(['title' => '<strong>Bold</strong> title']));
        $this->assertSame('Plain title', Give_Admin_Settings::get_field_title(['title' => 'Plain title']));
        $this->assertSame('Fish & Chips', Give_Admin_Settings::get_field_title(['title' => 'Fish & Chips']));
    }

    /**
     * @since TBD
     */
    public function testEmptyTitleStaysEmpty(): void
    {
        $this->assertSame('', Give_Admin_Settings::get_field_title(['title' => '']));
        $this->assertSame('0', Give_Admin_Settings::get_field_title(['title' => '0']));
    }
}
