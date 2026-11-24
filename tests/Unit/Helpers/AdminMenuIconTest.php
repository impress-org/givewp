<?php

namespace Give\Tests\Unit\Helpers;

use Give\Helpers\AdminMenuIcon;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class AdminMenuIconTest extends TestCase
{
    /**
     * WordPress only paints an icon with the admin color scheme when it is a base64 SVG data URI.
     *
     * @since TBD
     */
    public function testDataUriIsABase64Svg()
    {
        $uri = AdminMenuIcon::dataUri();

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $uri);

        $svg = base64_decode(substr($uri, strlen('data:image/svg+xml;base64,')), true);

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('fill="black"', $svg);
        $this->assertNotFalse(simplexml_load_string($svg));
    }

    /**
     * @since TBD
     */
    public function testGiveFormsPostTypeUsesTheIcon()
    {
        $this->assertSame(AdminMenuIcon::dataUri(), get_post_type_object('give_forms')->menu_icon);
    }
}
