<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\DoubleTheDonation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @since 3.16.0 Update to use FormMigrationProcessor trait
 * @since 3.8.0
 *
 * @covers \Give\FormMigration\Steps\DoubleTheDonation
 */
class TestDoubleTheDonation extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    public function testProcessShouldUpdateDoubleTheDonationBlockAttributes(): void
    {
        // Arrange
        $meta = [
            'give_dtd_label' => 'DTD Label',
            'dtd_enable_disable' => 'enabled',
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, DoubleTheDonation::class);

        // Assert
        $company = [
            'company_id'   => '',
            'company_name' => '',
            'entered_text' => '',
        ];
        $block = $v3Form->blocks->findByName('givewp/dtd');
        $this->assertSame($meta['give_dtd_label'], $block->getAttribute('label'));
        $this->assertEqualsIgnoringCase($company, $block->getAttribute('company'));
    }
}
