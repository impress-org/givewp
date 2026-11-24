<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalCommerce\Repositories;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\PayPalHttpClient;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;
use Give\PaymentGateways\PayPalCommerce\Repositories\Settings;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Helper_Form;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

/**
 * The create-order request body decides whether PayPal asks the donor for a shipping address. With
 * payment_source.card next to payment_source.paypal, PayPal ignores NO_SHIPPING and guest card payments
 * fail with SHIPPING_ADDRESS_MISSING, so the card source may only go out for card fields payments.
 *
 * @since TBD
 */
class PayPalOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testCreateOrderRequestsNoShippingThroughThePayPalSource(): void
    {
        $body = $this->createOrderAndCaptureBody();

        $experienceContext = $body['payment_source']['paypal']['experience_context'];

        $this->assertSame('NO_SHIPPING', $experienceContext['shipping_preference']);
        $this->assertSame('PAY_NOW', $experienceContext['user_action']);
        $this->assertArrayNotHasKey('application_context', $body);
    }

    /**
     * @since TBD
     */
    public function testCreateOrderOmitsTheCardSourceForSmartButtons(): void
    {
        $body = $this->createOrderAndCaptureBody();

        $this->assertArrayNotHasKey('card', $body['payment_source']);
    }

    /**
     * @since TBD
     */
    public function testCreateOrderSendsTheCardSourceForCardFieldsPayments(): void
    {
        $body = $this->createOrderAndCaptureBody(['cardFields' => true]);

        $this->assertSame(
            'SCA_WHEN_REQUIRED',
            $body['payment_source']['card']['attributes']['verification']['method']
        );
        $this->assertSame(
            'NO_SHIPPING',
            $body['payment_source']['paypal']['experience_context']['shipping_preference']
        );
    }

    /**
     * Runs createOrder() against a PayPal HTTP client that records the request instead of sending it.
     *
     * @since TBD
     */
    private function createOrderAndCaptureBody(array $overrides = []): array
    {
        $formId = Give_Helper_Form::create_simple_form()->ID;
        $body = null;

        $httpClient = $this->createMock(PayPalHttpClient::class);
        $httpClient->expects($this->once())
            ->method('execute')
            ->willReturnCallback(static function (OrdersCreateRequest $request) use (&$body) {
                $body = $request->body;

                return (object)['result' => (object)['id' => 'ORDER123']];
            });

        $payPalClient = $this->createMock(PayPalClient::class);
        $payPalClient->method('getHttpClient')->willReturn($httpClient);

        $merchant = MerchantDetail::fromArray([
            'merchantId' => 'merchant@example.com',
            'merchantIdInPayPal' => 'MERCHANT123',
            'clientId' => 'client-id',
            'clientSecret' => 'client-secret',
            'token' => [
                'scope' => '',
                'accessToken' => 'access-token',
                'tokenType' => 'Bearer',
                'appId' => '',
                'expiresIn' => 3600,
                'nonce' => '',
            ],
            'accountIsReady' => true,
            'supportsCustomPayments' => false,
            'accountCountry' => 'US',
        ]);

        $repository = new PayPalOrder($payPalClient, $merchant, give(Settings::class));

        $orderId = $repository->createOrder(array_merge([
            'formId' => $formId,
            'formTitle' => 'Test Donation Form',
            'donationAmount' => '25',
            'payer' => [
                'firstName' => 'Bill',
                'lastName' => 'Murray',
                'email' => 'billmurray@givewp.com',
            ],
        ], $overrides));

        $this->assertSame('ORDER123', $orderId);

        return $body;
    }
}
