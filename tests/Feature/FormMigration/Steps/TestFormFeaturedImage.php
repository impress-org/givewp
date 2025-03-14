<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\FormFeaturedImage;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @since 3.16.0
 *
 * @covers \Give\FormMigration\Steps\FormFeaturedImage
 */
class TestFormFeaturedImage extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @since 3.16.0
     */
    public function testSequoiaTemplateFeaturedImageIsMigratedCorrectly(): void
    {
        // Arrange
        $meta = [
            '_give_form_template' => 'sequoia',
            '_give_sequoia_form_template_settings' => [
                'introduction' => [
                    'image' => 'https://example.com/image.jpg',
                ],
            ],
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FormFeaturedImage::class);

        // Assert
        $this->assertSame('https://example.com/image.jpg', $v3Form->settings->designSettingsImageUrl);
        $this->assertSame('center', $v3Form->settings->designSettingsImageStyle);
    }

    /**
     * @since 3.16.0
     */
    public function testClassicTemplateHeaderBackgroundImageIsMigratedCorrectly(): void
    {
        // Arrange
        $meta = [
            '_give_form_template' => 'classic',
            '_give_classic_form_template_settings' => [
                'visual_appearance' => [
                    'header_background_image' => 'https://example.com/image.jpg',
                ],
            ],
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FormFeaturedImage::class);

        // Assert
        $this->assertSame('https://example.com/image.jpg', $v3Form->settings->designSettingsImageUrl);
    }

    /**
     * @since 3.16.0
     */
    public function testFallbackImageIsMigratedWhenFeaturedImageIsMissing(): void
    {
        // Arrange
        $v2Form = $this->createSimpleDonationForm();
        update_post_meta($v2Form->id, '_thumbnail_id', '1');
        add_filter('wp_get_attachment_image_src', function ($image, $attachmentId) {
            if ($attachmentId === 1) {
                return ['https://example.com/image.jpg', 100, 100, false];
            }

            return $image;
        }, 10, 2);

        // Act
        $v3Form = $this->migrateForm($v2Form, FormFeaturedImage::class);

        // Assert
        $this->assertSame('https://example.com/image.jpg', $v3Form->settings->designSettingsImageUrl);
    }

    /**
     * @since 3.16.0
     */
    public function testNoImageIsMigratedWhenNoImageExists ()
    {
        // Arrange
        $v2Form = $this->createSimpleDonationForm();

        // Act
        $v3Form = $this->migrateForm($v2Form, FormFeaturedImage::class);

        // Assert
        $this->assertEmpty($v3Form->settings->designSettingsImageUrl);
    }
}
