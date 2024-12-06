<?php

namespace Give\Tests\Feature\FormMigration;

use Give\FormMigration\FormMetaDecorator;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

/**
 * @since 3.4.0
 *
 * @covers \Give\FormMigration\FormMetaDecorator
 */
class TestFormMetaDecorator extends TestCase {
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @since 3.4.0
     */
    public function testIsLastNameRequiredShouldReturnTrue(): void
    {
        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => 'required',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->isLastNameRequired());

        give_update_option( 'last_name_field_required', 'required' );

        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => 'global',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->isLastNameRequired());
    }

    /**
     * @since 3.4.0
     */
    public function testIsLastNameRequiredShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => '',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->isLastNameRequired());

        give_update_option( 'last_name_field_required', 'optional' );

        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => 'global',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->isLastNameRequired());
    }

    /**
     * @since 3.4.0
     */
    public function testIsNameTitlePrefixEnabledShouldReturnTrue(): void
    {
        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_name_title_prefix' => 'required',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->isNameTitlePrefixEnabled());

        give_update_option( 'name_title_prefix', 'required' );

        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_name_title_prefix' => 'optional',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->isNameTitlePrefixEnabled());
    }

    /**
     * @since 3.4.0
     */
    public function testIsNameTitlePrefixEnabledShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_name_title_prefix' => 'disabled',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->isNameTitlePrefixEnabled());

        give_update_option( 'name_title_prefix', 'optional' );

        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_name_title_prefix' => 'disabled',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->isNameTitlePrefixEnabled());
    }

    /**
     * @since 3.4.0
     */
    public function testHasFundsShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->hasFunds());
    }

    /**
     * @since 3.4.0
     */
    public function testHasFundOptionsShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->hasFundOptions());
    }

    /**
     * @since 3.4.0
     */
    public function testHasFundsShouldReturnTrue(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @since 3.4.0
     */
    public function testHasFundOptionsShouldReturnTrue(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @since 3.5.0
     */
    public function testGetFormFeaturedImageForSequoiaTemplate()
    {
        $fakeImageUrl = 'https://example.com/image.jpg';

        $templateName = 'sequoia';
        $templateSettings = [
            'introduction' => [
                'image' => $fakeImageUrl,
            ],
        ];

        $formV2 = $this->createSimpleDonationForm([
            'meta' => [
                "_give_form_template" => $templateName,
                "_give_{$templateName}_form_template_settings" => $templateSettings,
            ],
        ]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        // Test 1 - Image is present in the form settings
        $this->assertEquals($fakeImageUrl, $formMetaDecorator->getFormFeaturedImage());

        // Test 2 - Image is NOT present in the form settings
        $templateSettings['introduction']['image'] = '';
        give_update_meta($formV2->id, "_give_{$templateName}_form_template_settings", $templateSettings);
        $this->assertEmpty($formMetaDecorator->getFormFeaturedImage());

        // Test 3 - The featured image from the WP default setting is used as a fallback
        $thumbnailId = $this->uploadTestImage();
        set_post_thumbnail($formV2->id, $thumbnailId);
        $this->assertNotEmpty($formMetaDecorator->getFormFeaturedImage());
    }

    /**
     * @since 3.5.0
     */
    public function testGetFormFeaturedImageForClassicTemplate()
    {
        $fakeImageUrl = 'https://example.com/image.jpg';

        $templateName = 'classic';
        $templateSettings = [
            'visual_appearance' => [
                'header_background_image' => $fakeImageUrl,
            ],
        ];

        $formV2 = $this->createSimpleDonationForm([
            'meta' => [
                "_give_form_template" => $templateName,
                "_give_{$templateName}_form_template_settings" => $templateSettings,
            ],
        ]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        // Test 1 - Image is present in the form settings
        $this->assertEquals($fakeImageUrl, $formMetaDecorator->getFormFeaturedImage());

        // Test 2 - Image is NOT present in the form settings
        $templateSettings['visual_appearance']['header_background_image'] = '';
        give_update_meta($formV2->id, "_give_{$templateName}_form_template_settings", $templateSettings);
        $this->assertEmpty($formMetaDecorator->getFormFeaturedImage());

        // Test 3 - The featured image from the WP default setting is NOT used as a fallback
        $thumbnailId = $this->uploadTestImage();
        set_post_thumbnail($formV2->id, $thumbnailId);
        $this->assertEmpty($formMetaDecorator->getFormFeaturedImage());
    }

    /**
     * @since 3.5.0
     */
    public function testGetFormFeaturedImageForLegacyTemplate()
    {
        $templateName = 'legacy';
        $templateSettings = [];

        $formV2 = $this->createSimpleDonationForm([
            'meta' => [
                "_give_form_template" => $templateName,
                "_give_{$templateName}_form_template_settings" => $templateSettings,
            ],
        ]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        // Test 1 - The featured image from the WP default setting is NOT set
        $this->assertEmpty($formMetaDecorator->getFormFeaturedImage());

        // Test 2 - The featured image from the WP default setting is set
        $thumbnailId = $this->uploadTestImage();
        set_post_thumbnail($formV2->id, $thumbnailId);
        $this->assertNotEmpty($formMetaDecorator->getFormFeaturedImage());
    }

    /**
     * @see https://core.trac.wordpress.org/browser/branches/4.5/tests/phpunit/tests/post/attachments.php#L24
     *
     * @since 3.5.0
     */
    private function uploadTestImage()
    {
        $filename = GIVE_PLUGIN_DIR . 'assets/src/images/give-placeholder.jpg';

        $contents = file_get_contents($filename);
        $upload = wp_upload_bits(basename($filename), null, $contents);

        return $this->_make_attachment($upload);
    }


    /**
     * @since 3.8.0
     */
    public function testIsDoubleTheDonationEnabledShouldReturnTrue(): void
    {
        $formV2 = $this->createSimpleDonationForm([
            'meta' => [
                'dtd_enable_disable' => 'enabled',
            ],
        ]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->getDoubleTheDonationStatus() === 'enabled');
    }

    /**
     * @since 3.8.0
     */
    public function testIsDoubleTheDonationDisabledShouldReturnTrue(): void
    {
        $formV2 = $this->createSimpleDonationForm([
            'meta' => [
                'dtd_enable_disable' => 'disabled',
            ],
        ]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->getDoubleTheDonationStatus() === 'disabled');
    }

    /**
     * @since 3.8.0
     */
    public function testIsDoubleTheDonationLabelSetShouldReturnTrue(): void
    {
        $formV2 = $this->createSimpleDonationForm([
            'meta' => [
                'give_dtd_label' => 'DTD Label',
            ],
        ]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->getDoubleTheDonationLabel() === 'DTD Label');
    }
}
