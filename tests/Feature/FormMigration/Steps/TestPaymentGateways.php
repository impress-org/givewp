<?php

namespace Feature\FormMigration\Steps;

use Give\FormMigration\Steps\PaymentGateways;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @unreleased
 *
 * @covers \Give\FormMigration\Steps\PaymentGateways
 */
class TestPaymentGateways extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testPaymentGatewaysProcess(): void
    {
        // Arrange
        $meta = [
            'give_stripe_per_form_accounts' => 'enabled',
            '_give_stripe_default_account' => 'acct_1',
            '_give_customize_offline_donations' => 'enabled',
            '_give_offline_checkout_notes' => 'Offline checkout notes',
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, PaymentGateways::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp/payment-gateways');
        $this->assertFalse($block->getAttribute('stripeUseGlobalDefault'));
        $this->assertSame('acct_1', $block->getAttribute('accountId'));
        $this->assertTrue($block->getAttribute('offlineEnabled'));
        $this->assertFalse($block->getAttribute('offlineUseGlobalInstructions'));
        $this->assertSame('Offline checkout notes', $block->getAttribute('offlineDonationInstructions'));
    }
}
