<?php

use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\CreditCardGateway;
use Give\PaymentGateways\Gateways\Stripe\LegacyStripePaymentIntent;

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

        $this->mock(LegacyStripePaymentIntent::class, function() {
           return new MockLegacyStripePaymentIntent( 'succeeded' );
        });

        $this->assertInstanceOf( PaymentProcessing::class, $gateway->createPayment( $this->getMockPaymentData() ) );
    }

    /** @test */
    public function it_creates_a_payment_that_requires_action()
    {
        $_POST['give_stripe_payment_method'] = 'pm_1234';
        $gateway = new CreditCardGateway();

        $this->mock(LegacyStripePaymentIntent::class, function() {
           return new MockLegacyStripePaymentIntent( 'requires_action' );
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

class MockLegacyStripePaymentIntent {
    public function __construct( $status ) { $this->status = $status; }
    public function create( $args ) { return $this; }
    public function id() { return 'pi_1234'; }
    public function status() { return $this->status; }
    public function nextActionRedirectUrl() { return 'https://wordpress.test/next-action'; }
}
