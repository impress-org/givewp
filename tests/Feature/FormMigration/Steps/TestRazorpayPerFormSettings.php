<?php

namespace Feature\FormMigration\Steps;

use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Steps\RazorpayPerFormSettings;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

/**
 * @unreleased
 */
class TestRazorpayPerFormSettings extends TestCase
{

    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @unreleased
     */
    public function testProcessShouldUpdatePaymentGatewaysBlockAttributes(): void
    {
        $liveKeyId = 'live_12304567890';
        $liveSecretKey = 'live_0123456789';
        $testKeyId = 'test_12304567890';
        $testSecretKey = 'test_0123456789';

        $meta = [
            'razorpay_per_form_account_options' => 'global',
            'razorpay_per_form_live_merchant_key_id' => $liveKeyId,
            'razorpay_per_form_live_merchant_secret_key' => $liveSecretKey,
            'razorpay_per_form_test_merchant_key_id' => $testKeyId,
            'razorpay_per_form_test_merchant_secret_key' => $testSecretKey,
        ];

        $formV2 = $this->createSimpleDonationForm(['meta' => $meta]);

        $payload = FormMigrationPayload::fromFormV2($formV2);

        $razorpayPerFormSettings = new RazorpayPerFormSettings($payload);
        $razorpayPerFormSettings->process();

        $paymentGatewaysBlock = $payload->formV3->blocks->findByName('givewp/payment-gateways');

        $this->assertSame(true, $paymentGatewaysBlock->getAttribute('useGlobalSettings'));
        $this->assertSame($liveKeyId, $paymentGatewaysBlock->getAttribute('liveKeyId'));
        $this->assertSame($liveSecretKey, $paymentGatewaysBlock->getAttribute('liveSecretKey'));
        $this->assertSame($testKeyId, $paymentGatewaysBlock->getAttribute('testKeyId'));
        $this->assertSame($testSecretKey, $paymentGatewaysBlock->getAttribute('testSecretKey'));
    }

}

