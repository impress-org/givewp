<?php

namespace Give\Tests\Feature\LegacyPaymentGateways;

use Exception;
use Give\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayAdapter;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use WPDieException;

class TestLegacyPaymentGatewayAdapter extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @unreleased
     * @throws Exception
     */
    public function testShouldCallGatewayController()
    {
        $legacyPaymentGatewayAdapter = $this->createMock(
            LegacyPaymentGatewayAdapter::class,
            function (PHPUnit_Framework_MockObject_MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['setSession']);

                return $mockBuilder->getMock();
            }
        );
        $donationForm = $this->createSimpleDonationForm();
        $registeredGateway = give(TestGateway::class);

        $legacyPaymentData = $this->getLegacyPaymentData([
            'gateway' => TestGateway::id(),
        ]);

        $legacyPaymentData['post_data']['give-form-id'] = $donationForm->id;
        $legacyPaymentData['post_data']['"give-form-id-prefix'] = "$donationForm->id-1";

        /** @var PHPUnit_Framework_MockObject_MockObject $legacyPaymentGatewayAdapter */
        $legacyPaymentGatewayAdapter->expects($this->once())
            ->method('setSession');

        try {
            //$legacyPaymentGatewayAdapter->handleBeforeGateway($legacyPaymentData, $registeredGateway);
        } catch (WPDieException $exception) {
            //ignore wp_die();
        }
    }

    /**
     * @unreleased
     */
    public function getLegacyPaymentData(array $data): array
    {
        return array_merge($data, [
            "price" => "25",
            "purchase_key" => "2aeb7a3560d36c72b4cf4ac8186de8d2",
            "user_email" => "jpwaldstein@gmail.com",
            "date" => "2024-02-02 09:45:20",
            "user_info" => [
                "id" => "1",
                "title" => "Mr.",
                "email" => "jpwaldstein@gmail.com",
                "first_name" => "Jonathan",
                "last_name" => "Waldstein",
                "address" => ""
            ],
            "post_data" => [
                "give-fee-amount" => "1.06",
                "give-fee-mode-enable" => "false",
                "give-fee-status" => "enabled",
                "give-honeypot" => "",
                "give-form-id-prefix" => "5807-1",
                "give-form-id" => "5807",
                "give-form-title" => "v2 Form gift aid",
                "give-current-url" => "https://givewp.test/donations/v2-form-gift-aid/",
                "give-form-url" => "https://givewp.test/give/v2-form-gift-aid/",
                "give-form-minimum" => "5.00",
                "give-form-maximum" => "999999.99",
                "give-form-hash" => "1231e62798",
                "give-price-id" => "1",
                "give-recurring-logged-in-only" => "",
                "give-logged-in-only" => "1",
                "give_recurring_donation_details" => '{"is_recurring":false}',
                "give-amount" => "25.00",
                "give_stripe_payment_method" => "",
                "give-fee-recovery-settings" => '{"fee_data":{"all_gateways":{"percentage":"2.900000","base_amount":"0.300000","give_fee_disable":false,"give_fee_status":true,"is_break_down":true,"maxAmount":"0"}},"give_fee_status":true,"give_fee_disable":false,"is_break_down":true,"fee_mode":"donor_opt_in","is_fee_mode":true,"fee_recovery":true}',
                "give_title" => "Mr.",
                "give_first" => "Jonathan",
                "give_last" => "Waldstein",
                "give_email" => "jpwaldstein@gmail.com",
                "payment-mode" => "manual",
                "p2pSourceID" => "",
                "p2pSourceType" => "",
                "give-user-id" => "1",
                "give_action" => "purchase",
                "give-gateway" => "manual",
                "give_embed_form" => "1"
            ],
            "gateway" => "manual",
            "card_info" => [
                "card_name" => "",
                "card_number" => "",
                "card_cvc" => "",
                "card_exp_month" => "",
                "card_exp_year" => "",
                "card_address" => "",
                "card_address_2" => "",
                "card_city" => "",
                "card_state" => "",
                "card_country" => "",
                "card_zip" => ""
            ],
            "gateway_nonce" => wp_create_nonce('give-gateway'),
        ]);
    }
}
