<?php

namespace Feature\FormMigration\Steps;

use Give\FormMigration\Steps\OfflineDonations;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @since 3.16.0
 *
 * @covers \Give\FormMigration\Steps\OfflineDonations
 */
class TestOfflineDonations extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @since 3.16.0
     */
    public function testOfflineDonationsProcessAddsBillingAddressFieldWhenEnabled(): void
    {
        // Arrange
        $meta = [
            '_give_customize_offline_donations' => 'custom',
            '_give_offline_donation_enable_billing_fields_single' => 'enabled',
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, OfflineDonations::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/billing-address');
        $this->assertNotNull($block);
    }

    /**
     * @since 3.16.0
     */
    public function testOfflineDonationsProcessMigratesNotes(): void
    {
        // Arrange
        $instructions = 'Please send a check to 123 Main St.';
        $meta = [
            '_give_customize_offline_donations' => 'custom',
            '_give_offline_checkout_notes' => $instructions,
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, OfflineDonations::class);

        // Assert
        $this->assertEquals($instructions, $v3Form->settings->offlineDonationInstructions);
    }
}
