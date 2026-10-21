<?php

namespace Give\Tests\Unit\Views\Form\Templates\Classic;

use Give\Tests\TestCase;

/**
 * @since 4.19.0.1
 */
final class StylesheetVariablesTest extends TestCase
{
    /**
     * @since 4.19.0.1
     */
    public function testPrimaryColorIsSanitizedAsAHexColor(): void
    {
        $html = $this->renderView('X" tabindex="0" onfocus="alert(1)" y="');

        // A value that is not a hex colour is dropped, so it cannot land in the stylesheet.
        $this->assertStringNotContainsString('onfocus="alert(1)"', $html);
        $this->assertStringContainsString('--give-primary-color: ;', $html);
    }

    /**
     * @since 4.19.0.1
     */
    public function testValidHexColorIsKept(): void
    {
        $html = $this->renderView('#1E8CBE');

        $this->assertStringContainsString('--give-primary-color: #1E8CBE;', $html);
    }

    /**
     * Render the Classic stylesheet-variables partial with the given primary_color. The partial
     * reads its values from local scope, so it is required under output buffering.
     *
     * @since 4.19.0.1
     */
    private function renderView(string $primaryColor): string
    {
        $headerBackgroundImage = '';
        $headerBackgroundColor = '';
        $statsProgressBarColor = '#000000';
        $primaryFont           = 'Arial';

        ob_start();
        require GIVE_PLUGIN_DIR . 'src/Views/Form/Templates/Classic/resources/css/variables.php';

        return (string) ob_get_clean();
    }
}
