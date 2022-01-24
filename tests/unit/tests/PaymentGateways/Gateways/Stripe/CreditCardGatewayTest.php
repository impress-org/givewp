<?php

use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\CreditCardGateway;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 */
class CreditCardGatewayTest extends Give_Unit_Test_Case
{
    public function testCreatePaymentProcessing()
    {
        $_POST['give_stripe_payment_method'] = 'pm_1234';
        $gateway = new CreditCardGateway();

        $this->assertInstanceOf( PaymentProcessing::class, $gateway->createPayment( $this->getMockPaymentData() ) );
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
    public function create( $args ) {
        return new class {
            public string $id = 'pi_1234';
            public string $status = 'succeeded';
        };
    }
}
