<?php

namespace Give\Tests\Unit\Forms;

use Give\Tests\TestCase;

/**
 * @since 4.19.1
 */
class GiveFormDisplayContentTest extends TestCase
{
    /**
     * @since 4.19.1
     */
    public function testKeepsEmbeddedIframesInTheFormContent(): void
    {
        $formId = wp_insert_post(['post_type' => 'give_forms', 'post_status' => 'publish', 'post_title' => 'Form']);
        give_update_meta($formId, '_give_form_content', '<iframe src="https://player.vimeo.com/video/76979871" width="640" height="360" allowfullscreen></iframe>');

        ob_start();
        give_form_display_content($formId, []);
        $output = ob_get_clean();

        $this->assertStringContainsString('<iframe', $output);
        $this->assertStringContainsString('src="https://player.vimeo.com/video/76979871"', $output);
    }

    /**
     * @since 4.19.1
     */
    public function testKeepsMarkupAddedThroughTheContentFilter(): void
    {
        $formId = wp_insert_post(['post_type' => 'give_forms', 'post_status' => 'publish', 'post_title' => 'Form']);
        $filter = static function () {
            return '<form class="nested-give-form"><input type="hidden" name="give-form-id" value="1"></form>';
        };
        add_filter('give_form_content_output', $filter);

        ob_start();
        give_form_display_content($formId, []);
        $output = ob_get_clean();

        remove_filter('give_form_content_output', $filter);

        $this->assertStringContainsString('<input type="hidden" name="give-form-id" value="1">', $output);
    }
}
