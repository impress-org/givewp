<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\ConstantContact;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @unreleased Update to use FormMigrationProcessor trait
 * @since 3.7.0
 */
class TestConstantContact extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter, FormMigrationProcessor;

    /**
     * @unreleased Update test to use FormMigrationProcessor::migrateForm method
     * @since 3.7.0
     */
    public function testFormMigratesUsingGlobalSettingsWhenGloballyEnabled(): void
    {
        // Arrange
        $options = [
            'give_constant_contact_show_checkout_signup' => 'on',
            'give_constant_contact_label' => 'Subscribe to our newsletter?',
            'give_constant_contact_checked_default' => 'on',
            'give_constant_contact_list' => ['1928414891'],
        ];
        foreach ($options as $key => $value) {
            give_update_option($key, $value);
        }
        $meta = ['_give_constant_contact_disabled' => 'false'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ConstantContact::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/constantcontact');
        $this->assertTrue(true, $block->getAttribute('checked' === 'on'));
        $this->assertSame($options['give_constant_contact_label'], $block->getAttribute('label'));
        $this->assertSame($options['give_constant_contact_list'], $block->getAttribute('selectedEmailLists'));
    }

    /**
     * @unreleased
     */
    public function testFormConfiguredToDisableConstantContactIsMigratedWithoutConstantContactBlock()
    {
        // Arrange
        give_update_option('give_constant_contact_show_checkout_signup', 'on');
        $meta = ['_give_constant_contact_disable' => 'true'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ConstantContact::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/constantcontact');
        $this->assertNull($block);
    }

    /**
     * @unreleased Update test to use FormMigrationProcessor::migrateForm method
     * @since 3.7.0
     */
    public function testFormConfiguredToUseCustomizedConstantContactSettingsIsMigrated(): void
    {
        // Arrange
        $meta = [
            '_give_constant_contact_enable' => 'true',
            '_give_constant_contact_custom_label' => 'Subscribe to our newsletter?',
            '_give_constant_contact_checked_default' => 'on',
            '_give_constant_contact' => ['1928414891'],
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, ConstantContact::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/constantcontact');
        $this->assertTrue(true, $block->getAttribute('checked' === 'on'));
        $this->assertSame($meta['_give_constant_contact_custom_label'], $block->getAttribute('label'));
        $this->assertSame($meta['_give_constant_contact'], $block->getAttribute('selectedEmailLists'));
    }
}
