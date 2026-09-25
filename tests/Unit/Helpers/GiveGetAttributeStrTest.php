<?php

namespace Give\Tests\Unit\Helpers;

use Give\Tests\TestCase;

/**
 * @since TBD
 */
class GiveGetAttributeStrTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testEscapesEveryAttributeValue(): void
    {
        $output = give_get_attribute_str([
            'class' => 'a"b',
            'data-label' => '<script>x</script>',
            'value' => 'v"',
        ]);

        $this->assertSame(
            'class="a&quot;b" data-label="&lt;script&gt;x&lt;/script&gt;" value="v&quot;"',
            $output
        );
    }

    /**
     * @since TBD
     */
    public function testTooltipLabelIsEscapedInAriaLabel(): void
    {
        $markup = Give()->tooltips->render_help('Say "hi" <b>');

        $this->assertStringContainsString('aria-label="Say &quot;hi&quot; &lt;b&gt;"', $markup);
        $this->assertStringNotContainsString('aria-label="Say "hi"', $markup);
    }
}
