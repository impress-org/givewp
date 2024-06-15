<?php

namespace Feature\FormMigration\Steps;

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\ConvertKit;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

class TestConvertKit extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter, FormMigrationProcessor;

    /**
     * @unreleased Update test to use FormMigrationProcessor::migrateForm method
     * @since 3.11.0
     */
    public function testFormConfiguredToUseGlobalConvertKitSettingsMigratesUsingGlobalSettingsWhenGloballyEnabled()
    {
        // Arrange
        $options = [
            'give_convertkit_show_subscribe_checkbox' => 'enabled',
            'give_convertkit_label' => __('Subscribe to newsletter?', 'give'),
            'give_convertkit_list' => '6352843',
            '_give_convertkit_tags' => ['4619079', '4619080'],
            'give_convertkit_checked_default' => true,
        ];
        foreach ($options as $key => $value) {
            give_update_option($key, $value);
        }
        $meta = ['_give_convertkit_override_option' => 'default'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ConvertKit::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp-convertkit/convertkit');
        $this->assertSame($options['give_convertkit_label'], $block->getAttribute('label'));
        $this->assertSame($options['give_convertkit_list'], $block->getAttribute('selectedForm'));
        $this->assertSame($options['_give_convertkit_tags'], $block->getAttribute('tagSubscribers'));
        $this->assertTrue(true, $block->getAttribute('defaultChecked'));
    }

    /**
     * @unreleased
     */
    public function testFormConfiguredToUseGlobalConvertKitSettingsIsMigratedWithoutConvertKitBlockWhenNotGloballyEnabled()
    {
        // Arrange
        give_update_option('give_convertkit_show_subscribe_checkbox', 'disabled');
        $meta = ['_give_convertkit_override_option' => 'default'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ConvertKit::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp-convertkit/convertkit');
        $this->assertNull($block);
    }

    /**
     * @unreleased
     */
    public function testFormConfiguredToDisableConvertKitIsMigratedWithoutConvertKitBlock()
    {
        // Arrange
        $meta = ['_give_convertkit_override_option' => 'disabled'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ConvertKit::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp-convertkit/convertkit');
        $this->assertNull($block);
    }

    /**
     * @unreleased Update test to use FormMigrationProcessor::migrateForm method
     * @since 3.11.0
     */
    public function testFormConfiguredToUseCustomizedConvertKitSettingsIsMigrated()
    {
        // Arrange
        $meta = [
            '_give_convertkit_override_option' => 'customize',
            '_give_convertkit_custom_label' => __('Subscribe to newsletter?' , 'give'),
            '_give_convertkit' => '6352843',
            '_give_convertkit_tags' => ['4619079', '4619080'],
            '_give_convertkit_checked_default' => true,
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ConvertKit::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp-convertkit/convertkit');
        $this->assertSame($meta['_give_convertkit_custom_label'], $block->getAttribute('label'));
        $this->assertSame($meta['_give_convertkit_tags'], $block->getAttribute('tagSubscribers'));
        $this->assertSame($meta['_give_convertkit'], $block->getAttribute('selectedForm'));
        $this->assertTrue(true, $block->getAttribute('defaultChecked'));
    }
}
