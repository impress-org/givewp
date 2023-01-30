<?php

namespace Give\Tests\Feature\Gateways;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\Support\ValueObjects\Money;
use Give\NextGen\DonationForm\Actions\GenerateDonationConfirmationReceiptUrl;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\NextGenStripeGateway;
use Give\PaymentGateways\Gateways\Stripe\Actions\SaveDonationSummary;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;

class NextGenStripeGatewayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws ApiErrorException
     * @throws Exception
     */
    public function testFormSettingsShouldReturnData()
    {
        $mockGateway = $this->getMockGateway();

        $form = DonationForm::factory()->create();
        $currency = give_get_currency($form->id);
        $formDefaultAmount = give_get_default_form_amount($form->id);
        $amount = Money::fromDecimal(!empty($formDefaultAmount) ? $formDefaultAmount : '50', $currency);
        $stripePublishableKey = 'stripe-publishable-key';
        $stripeConnectedAccountKey = 'stripe-connected-account-key';

        $stripePaymentIntent = $this->getMockIntent($amount, $stripePublishableKey);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->method('generateStripePaymentIntent')
            ->willReturn($stripePaymentIntent);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->method('getStripePublishableKey')
            ->willReturn($stripePublishableKey);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->method('getStripeConnectedAccountKey')
            ->willReturn($stripeConnectedAccountKey);

        /** @var NextGenStripeGateway $mockGateway */
        $settings = $mockGateway->formSettings($form->id);

        $this->assertSame($settings, [
            'stripeKey' => $stripePublishableKey,
            'stripeClientSecret' => $stripePaymentIntent->client_secret,
            'stripeConnectedAccountKey' => $stripeConnectedAccountKey,
            'stripePaymentIntentId' => $stripePaymentIntent->id,
        ]);
    }

    /**
     * @unreleased
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
        $stripeConnectedAccountKey = 'stripe-connected-account-key';
        $stripePaymentIntent = $this->getMockIntent($donation->amount, $stripePublishableKey);

        $mockGateway = $this->getMockGateway([
            'getOrCreateStripeCustomerFromDonation',
            'getPaymentIntentArgsFromDonation',
            'updateStripePaymentIntent',
            'updateDonationMetaFromPaymentIntent'
        ]);

        $mockCustomer = Customer::constructFrom([
            'id' => 'stripe-customer-id',
            'name' => "$donation->firstName $donation->lastName",
            'email' => $donation->email,
            ['stripe_account' => $stripeConnectedAccountKey]
        ]);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('getOrCreateStripeCustomerFromDonation')
            ->with($stripeConnectedAccountKey, $donation)
            ->willReturn($mockCustomer);

        $intentArgs = [
            'amount' => $donation->amount->formatToMinorAmount(),
            'customer' => $mockCustomer->id,
            'description' => (new SaveDonationSummary)($donation)->getSummaryWithDonor(),
            'metadata' => give_stripe_prepare_metadata($donation->id),
        ];

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('getPaymentIntentArgsFromDonation')
            ->with($donation, $mockCustomer)
            ->willReturn($intentArgs);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('updateStripePaymentIntent')
            ->with($stripePaymentIntent->id, $intentArgs)
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
            'stripePaymentIntentId' => $stripePaymentIntent->id,
            'stripeConnectedAccountKey' => $stripeConnectedAccountKey,
            'successUrl' => $redirectReturnUrl,
        ];

        $response = $mockGateway->createPayment($donation, $gatewayData);

        $this->assertEquals(
            $response,
            new RespondToBrowser([
                'intentStatus' => $stripePaymentIntent->status,
                'returnUrl' => $gatewayData['successUrl'],
            ])
        );
    }

    /**
     * @unreleased
     */
    protected function getMockGateway(array $methods = [])
    {
        return $this->createMock(
            NextGenStripeGateway::class,
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
     * @unreleased
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
