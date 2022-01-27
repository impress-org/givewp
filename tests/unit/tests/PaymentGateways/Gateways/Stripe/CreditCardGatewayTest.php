<?php

use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\CreditCardGateway;

/**
 * @unreleased
 */
class CreditCardGatewayTest extends Give_Unit_Test_Case
{
    /** @test */
    public function it_creates_a_payment_that_is_processing()
    {
        $_POST['give_stripe_payment_method'] = 'pm_1234';
        $gateway = new CreditCardGateway();

        $this->mock(Give_Stripe_Payment_Intent::class, function() {
           return new Give_Stripe_Payment_Intent( 'succeeded' );
        });

        $this->assertInstanceOf( PaymentProcessing::class, $gateway->createPayment( $this->getMockPaymentData() ) );
    }

    /** @test */
    public function it_creates_a_payment_that_requires_action()
    {
        $_POST['give_stripe_payment_method'] = 'pm_1234';
        $gateway = new CreditCardGateway();

        $this->mock(Give_Stripe_Payment_Intent::class, function() {
           return new Give_Stripe_Payment_Intent( 'requires_action' );
        });

        $this->assertInstanceOf( RedirectOffsite::class, $gateway->createPayment( $this->getMockPaymentData() ) );
    }

    public function getMockPaymentData()
    {
        $paymentData = new GatewayPaymentData;
        $paymentData->donationId = 0;
        $paymentData->price = '1.00';
        $paymentData->currency = 'USD';
        $paymentData->donorInfo = new \Give\ValueObjects\DonorInfo();
        $paymentData->donorInfo->email = 'tester@test.test';
        return $paymentData;
    }
}

class Give_Stripe_Customer {
    public function get_id() {
        return 'cust_1234';
    }
}

class Give_Stripe_Payment_Intent {
    protected $status;
    public function __construct( $status ) {
        $this->status = $status;
    }
    public function create() {
        return json_decode(json_encode([
            'id' => 'pi_1234',
            'status' => $this->status,
            'client_secret' => 'pi_secret',
            'next_action' => [
                'redirect_to_url' => [
                    'url' => '',
                ]
            ]
        ]));
    }
}
