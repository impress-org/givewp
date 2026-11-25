<?php

namespace Give\Tests\Unit\LegacySettings;

use Give\Tests\TestCase;
use Give_Admin_Settings;

/**
 * @since 4.20.0
 */
final class SettingsFieldTitleTest extends TestCase
{
    /**
     * @since 4.20.0
     */
    public function testTitleWithTextIsReturnedAsGiven(): void
    {
        $this->assertSame('<strong>Bold</strong> title', Give_Admin_Settings::get_field_title(['title' => '<strong>Bold</strong> title']));
        $this->assertSame('Plain title', Give_Admin_Settings::get_field_title(['title' => 'Plain title']));
        $this->assertSame('Fish & Chips', Give_Admin_Settings::get_field_title(['title' => 'Fish & Chips']));
    }

    /**
     * @since 4.20.0
     */
    public function testEmptyTitleStaysEmpty(): void
    {
        $this->assertSame('', Give_Admin_Settings::get_field_title(['title' => '']));
        $this->assertSame('0', Give_Admin_Settings::get_field_title(['title' => '0']));
    }
}
