<?php

namespace Give\Tests\Feature\Shortcodes;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class TestFormGridFeaturedImage extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        give_update_option('form_featured_img', 'enabled');
    }

    /**
     * @since TBD
     *
     * @dataProvider columnsProvider
     */
    public function testFeaturedImageRendersForAttachmentWithoutAttachedFileMeta(string $columns): void
    {
        $imageUrl = 'https://example.com/wp-content/uploads/form-grid-image.jpg';

        /*
         * The Form Builder image picker stores a raw URL. An attachment created without
         * wp_generate_attachment_metadata() has no _wp_attached_file meta, so attachment_url_to_postid()
         * cannot resolve that URL and only the guid holds it.
         */
        wp_insert_attachment([
            'guid' => $imageUrl,
            'post_title' => 'Form image',
            'post_mime_type' => 'image/jpeg',
            'post_status' => 'inherit',
        ]);

        $form = DonationForm::factory()->create([
            'settings' => FormSettings::fromArray(['designSettingsImageUrl' => $imageUrl]),
        ]);

        $output = give_form_grid_shortcode(['ids' => (string)$form->id, 'columns' => $columns]);

        $this->assertStringContainsString($imageUrl, $output);
    }

    /**
     * @since TBD
     */
    public function columnsProvider(): array
    {
        return [
            'one column' => ['1'],
            'two columns' => ['2'],
            'three columns' => ['3'],
            'four columns' => ['4'],
        ];
    }
}
