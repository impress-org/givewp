<?php

namespace Give\Tests\Unit\Helpers;

use Give\Tests\TestCase;

/**
 * @since 4.19.1
 */
class GiveTooltipsTest extends TestCase
{
    /**
     * @since 4.19.1
     */
    public function testPrintRenderHelpEchoesRenderHelpOutput(): void
    {
        $args = 'Say "hi" <b>';

        $this->expectOutputString(Give()->tooltips->render_help($args));

        Give()->tooltips->print_render_help($args);
    }

    /**
     * @since 4.19.1
     */
    public function testRenderHelpDoesNotDoubleEscapeAnAlreadyEscapedLabel(): void
    {
        $label = esc_attr("Tom's & Jerry");

        $markup = Give()->tooltips->render_help($label);

        $this->assertStringContainsString('aria-label="Tom&#039;s &amp; Jerry"', $markup);
        $this->assertStringNotContainsString('&amp;#039;', $markup);
        $this->assertStringNotContainsString('&amp;amp;', $markup);
    }
}
