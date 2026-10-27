<?php

namespace Give\Tests\Unit\Views\Form\Templates\Sequoia;

use Give\Tests\TestCase;

/**
 * @since 4.19.0.1
 */
final class LoadingViewTest extends TestCase
{
    /**
     * @since 4.19.1 Expect the primary color reduced to a hex value.
     * @since 4.19.0.1
     */
    public function testPrimaryColorIsEscapedForTheStyleAttribute(): void
    {
        // A primary_color value carrying characters significant in an HTML attribute context.
        $input  = 'X" tabindex="0" onfocus="alert(1)" y="';
        $formId = $this->createFormWithPrimaryColor($input);

        $html = $this->renderView($formId);

        // The raw double-quote must not survive to break out of the surrounding style attribute.
        $this->assertStringNotContainsString('onfocus="alert(1)"', $html);

        // An invalid hex color falls back to the default.
        $this->assertStringContainsString('%2328C77B', $html);
    }

    /**
     * @since 4.19.0.1
     */
    private function createFormWithPrimaryColor(string $primaryColor): int
    {
        $formId = wp_insert_post(
            [
                'post_type'   => 'give_forms',
                'post_status' => 'publish',
                'post_title'  => 'Loading view test form',
            ]
        );

        Give()->form_meta->update_meta($formId, '_give_form_template', 'sequoia');
        Give()->form_meta->update_meta(
            $formId,
            '_give_sequoia_form_template_settings',
            ['visual_appearance' => ['primary_color' => $primaryColor]]
        );

        return $formId;
    }

    /**
     * Render the Sequoia loading view for the given form. The view reads $formId from local
     * scope, so it is required under output buffering.
     *
     * @since 4.19.0.1
     */
    private function renderView(int $formId): string
    {
        ob_start();
        require GIVE_PLUGIN_DIR . 'src/Views/Form/Templates/Sequoia/views/loading.php';

        return (string) ob_get_clean();
    }
}
