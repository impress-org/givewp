<?php

namespace Give\Tests\Feature\Gateways\Stripe\StripePaymentElement;

use Exception;
use Give\DonationForms\Actions\GenerateDonationConfirmationReceiptUrl;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\Stripe\Actions\SaveDonationSummary;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\DataTransferObjects\StripePaymentIntentData;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;

class TestStripePaymentElementGateway extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     *
     * @throws ApiErrorException
     * @throws Exception
     */
    public function testFormSettingsShouldReturnData()
    {
        $mockGateway = $this->getMockGateway();

        $form = DonationForm::factory()->create();
        $stripePublishableKey = 'stripe-publishable-key';
        $stripeConnectedAccountKey = 'stripe-connected-account-key';

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->method('getStripePublishableKey')
            ->willReturn($stripePublishableKey);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->method('getStripeConnectedAccountKey')
            ->willReturn($stripeConnectedAccountKey);

        /** @var StripePaymentElementGateway $mockGateway */
        $settings = $mockGateway->formSettings($form->id);

        $this->assertSame($settings, [
            'formId' => $form->id,
            'stripeKey' => $stripePublishableKey,
            'stripeConnectedAccountId' => $stripeConnectedAccountKey,
        ]);
    }

    /**
     * @since 3.0.0
     *
     * @throws \Give\Framework\Exceptions\Primitives\Exception
     * @throws PaymentGatewayException
     * @throws ApiErrorException
     */
    public function testCreatePaymentShouldReturnRespondToBrowserCommand()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();
        /** @var Donation $donation */
        $donation = Donation::factory()->create(['formId' => $form->id]);
        $stripePublishableKey = 'stripe-publishable-key';
        $stripeConnectedAccountId = 'stripe-connected-account-id';
        $stripePaymentIntent = $this->getMockIntent($donation->amount, $stripePublishableKey);

        $mockGateway = $this->getMockGateway([
            'getOrCreateStripeCustomerFromDonation',
            'getPaymentIntentDataFromDonation',
            'updateDonationMetaFromPaymentIntent'
        ]);

        $mockCustomer = Customer::constructFrom([
            'id' => 'stripe-customer-id',
            'name' => "$donation->firstName $donation->lastName",
            'email' => $donation->email,
            ['stripe_account' => $stripeConnectedAccountId]
        ]);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('getOrCreateStripeCustomerFromDonation')
            ->with($stripeConnectedAccountId, $donation)
            ->willReturn($mockCustomer);

        $intentData = StripePaymentIntentData::fromArray([
            'amount' => $donation->amount->formatToMinorAmount(),
            'customer' => $mockCustomer->id,
            'description' => (new SaveDonationSummary)($donation)->getSummaryWithDonor(),
            'metadata' => give_stripe_prepare_metadata($donation->id),
            'currency' => $donation->amount->getCurrency()->getCode(),
            'statement_descriptor' => 'statement-descriptor',
        ]);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('getPaymentIntentDataFromDonation')
            ->with($donation, $mockCustomer)
            ->willReturn($intentData);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('generateStripePaymentIntent')
            ->with($stripeConnectedAccountId, $intentData)
            ->willReturn($stripePaymentIntent);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('updateDonationMetaFromPaymentIntent')
            ->with($donation, $stripePaymentIntent);

        $redirectReturnUrl = (new GenerateDonationConfirmationReceiptUrl())(
            $donation,
            'https://givewp.com',
            '123'
        );

        $gatewayData = [
            'stripeConnectedAccountId' => $stripeConnectedAccountId,
            'successUrl' => $redirectReturnUrl,
            'stripePaymentMethod' => 'card',
            'stripePaymentMethodIsCreditCard' => true
        ];

        $response = $mockGateway->createPayment($donation, $gatewayData);

        $this->assertEquals(
            $response,
            new RespondToBrowser([
                'clientSecret' => $stripePaymentIntent->client_secret,
                'returnUrl' => $gatewayData['successUrl'],
                'billingDetails' => [
                    'name' => trim("$donation->firstName $donation->lastName"),
                    'email' => $donation->email
                ]
            ])
        );
    }

    /**
     * @since 3.0.0
     */
    protected function getMockGateway(array $methods = [])
    {
        return $this->createMock(
            StripePaymentElementGateway::class,
            function (PHPUnit_Framework_MockObject_MockBuilder $mockBuilder) use ($methods) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(
                    array_merge(
                        ['generateStripePaymentIntent', 'getStripePublishableKey', 'getStripeConnectedAccountKey'],
                        $methods
                    )
                );

                return $mockBuilder->getMock();
            }
        );
    }

    /**
     * @since 3.0.0
     */
    protected function getMockIntent(Money $amount, string $stripePublishableKey): PaymentIntent
    {
        return PaymentIntent::constructFrom([
            'id' => 'stripe-payment-intent-id',
            'amount' => $amount->formatToMinorAmount(),
            'currency' => $amount->getCurrency()->getCode(),
            'automatic_payment_methods' => ['enabled' => true],
            'client_secret' => 'client-secret',
            'status' => 'succeeded'
        ],
            ['stripe_account' => $stripePublishableKey]
        );
    }
}
