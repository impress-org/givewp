<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\FeeRecovery;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @since 3.16.0
 *
 * @covers \Give\FormMigration\Steps\FeeRecovery
 */
class TestFeeRecovery extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @since 3.16.0
     */
    public function testFeeRecoveryProcessWithGlobalSettings(): void
    {
        // Arrange
        $options = [
            'give_fee_recovery' => 'enabled',
            'give_fee_configuration' => 'all_gateways',
            'give_fee_percentage' => 5,
            'give_fee_base_amount' => 0.50,
            'give_fee_maximum_fee_amount' => 20.00,
            'give_fee_breakdown' => 'enabled',
            'give_fee_mode' => 'donor_opt_in',
            'give_fee_checkbox_label' => 'Fee Recovery checkbox label',
            'give_fee_explanation' => 'Message for fee recovery',
        ];
        foreach ($options as $key => $value) {
            give_update_option($key, $value);
        }
        $meta = ['_form_give_fee_recovery' => 'global'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FeeRecovery::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp-fee-recovery/fee-recovery');
        $this->assertSame(true, $block->getAttribute('useGlobalSettings'));
        $this->assertSame(true, $block->getAttribute('feeSupportForAllGateways'));
        $this->assertSame([], $block->getAttribute('perGatewaySettings'));
        $this->assertSame(5.0, $block->getAttribute('feePercentage'));
        $this->assertSame(0.5, $block->getAttribute('feeBaseAmount'));
        $this->assertSame(20.0, $block->getAttribute('maxFeeAmount'));
        $this->assertSame(true, $block->getAttribute('includeInDonationSummary'));
        $this->assertSame(true, $block->getAttribute('donorOptIn'));
        $this->assertSame('Fee Recovery checkbox label', $block->getAttribute('feeCheckboxLabel'));
        $this->assertSame('Message for fee recovery', $block->getAttribute('feeMessage'));
    }

    /**
     * @since 3.16.0
     */
    public function testFeeRecoveryProcessWithPerFormSettings(): void
    {
        // Arrange
        $meta = [
            '_form_give_fee_recovery' => 'enabled',
            '_form_give_fee_configuration' => 'all_gateways',
            '_form_give_fee_percentage' => 5,
            '_form_give_fee_base_amount' => 0.50,
            '_form_give_fee_maximum_fee_amount' => 20.00,
            '_form_breakdown' => 'enabled',
            '_form_give_fee_mode' => 'donor_opt_in',
            '_form_give_fee_checkbox_label' => 'Fee Recovery checkbox label',
            '_form_give_fee_explanation' => 'Message for fee recovery',
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FeeRecovery::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp-fee-recovery/fee-recovery');
        $this->assertSame(false, $block->getAttribute('useGlobalSettings'));
        $this->assertSame(true, $block->getAttribute('feeSupportForAllGateways'));
        $this->assertSame(5.0, $block->getAttribute('feePercentage'));
        $this->assertSame(0.5, $block->getAttribute('feeBaseAmount'));
        $this->assertSame(20.0, $block->getAttribute('maxFeeAmount'));
        $this->assertSame(true, $block->getAttribute('includeInDonationSummary'));
        $this->assertSame(true, $block->getAttribute('donorOptIn'));
        $this->assertSame('Fee Recovery checkbox label', $block->getAttribute('feeCheckboxLabel'));
        $this->assertSame('Message for fee recovery', $block->getAttribute('feeMessage'));
    }

    /**
     * @since 3.16.0
     */
    public function testFeeRecoveryProcessWithGlobalSettingsDisabled(): void
    {
        // Arrange
        give_update_option('give_fee_recovery', 'disabled');
        $meta = ['_form_give_fee_recovery' => 'global'];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FeeRecovery::class);

        // Assert
        $block = $v3Form->blocks->findByName('givewp-fee-recovery/fee-recovery');
        $this->assertNull($block);
    }
}
