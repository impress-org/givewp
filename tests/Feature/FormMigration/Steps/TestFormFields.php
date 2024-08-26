<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\FormFields;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @unreleased
 *
 * @covers \Give\FormMigration\Steps\FormFields
 */
class TestFormFields extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testDonorNameFormFieldProcess(): void
    {
        // Arrange
        $options = [
            'title_prefixes' => ['Mr.', 'Mrs.', 'Ms.', 'Dr.'],
        ];
        foreach ($options as $key => $value) {
            give_update_option($key, $value);
        }
        $meta = [
            '_give_name_title_prefix' => 'required',
            '_give_title_prefixes' => ['Mr.', 'Mrs.', 'Ms.', 'Dr.'],
            '_give_last_name_field_required' => 'required',
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FormFields::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/donor-name');
        $this->assertTrue($block->getAttribute('showHonorific'));
        $this->assertEquals($options['title_prefixes'], $block->getAttribute('honorifics'));
        $this->assertTrue($block->getAttribute('requireLastName'));
    }

    /**
     * @unreleased
     */
    public function testDonorCommentsFormFieldProcess()
    {
        // Arrange
        $meta = [
            '_give_donor_comment' => 'enabled',
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FormFields::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/donor-comments');
        $this->assertNotNull($block);
    }

    /**
     * @unreleased
     */
    public function testAnonymousDonationsFormFieldProcess()
    {
        // Arrange
        $meta = [
            '_give_anonymous_donation' => 'enabled',
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FormFields::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/anonymous');
        $this->assertNotNull($block);
    }
}
