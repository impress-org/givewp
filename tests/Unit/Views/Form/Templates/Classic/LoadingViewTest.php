<?php

namespace Give\Tests\Unit\Views\Form\Templates\Classic;

use Give\Tests\TestCase;

/**
 * @since TBD
 */
final class LoadingViewTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testPrimaryColorIsEscapedForTheStyleAttribute(): void
    {
        // A primary_color value carrying characters significant in an HTML attribute context.
        $input = 'X" tabindex="0" onfocus="alert(1)" y="';
        $html  = $this->renderView($input);

        // The raw double-quote must not survive to break out of the surrounding style attribute.
        $this->assertStringNotContainsString('onfocus="alert(1)"', $html);

        // The value is present, escaped for the attribute context (quotes encoded).
        $this->assertStringContainsString(esc_attr(trim($input, '#')), $html);
    }

    /**
     * Render the Classic loading view with the given primary_color. The view reads $options
     * from local scope, so it is required under output buffering with a crafted array.
     *
     * @since TBD
     */
    private function renderView(string $primaryColor): string
    {
        $options = [
            'primary_color' => $primaryColor,
        ];

        ob_start();
        require GIVE_PLUGIN_DIR . 'src/Views/Form/Templates/Classic/resources/views/loading.php';

        return (string) ob_get_clean();
    }
}
