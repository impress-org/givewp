<?php

namespace Give\Tests\Unit\LegacyMisc;

use Give\Tests\TestCase;

/**
 * @since 4.20.0
 */
class GiveGetSafeAssetUrlTest extends TestCase
{
    /**
     * @since 4.20.0
     */
    public function testRemovesTheSchemeAndKeepsTheRestOfTheUrl(): void
    {
        $this->assertSame('//example.org/wp-content/a.js', give_get_safe_asset_url('https://example.org/wp-content/a.js'));
        $this->assertSame('//example.org/b.css?ver=1', give_get_safe_asset_url('http://example.org/b.css?ver=1'));
        $this->assertSame('', give_get_safe_asset_url(''));
    }
}
