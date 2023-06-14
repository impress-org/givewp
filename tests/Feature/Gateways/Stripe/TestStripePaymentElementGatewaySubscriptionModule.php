<?php

namespace Give\Tests\Feature\Gateways\Stripe;

use Exception;
use Give\DonationForms\Actions\GenerateDonationConfirmationReceiptUrl;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGatewaySubscriptionModule;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use Stripe\Customer;
use Stripe\Plan;
use Stripe\Product;

class TestStripePaymentElementGatewaySubscriptionModule extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 0.3.0
     *
     * @throws Exception
     */
    public function testCreateSubscriptionShouldReturnRespondToBrowserCommand()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation([
            'donationFormId' => $form->id,
        ]);

        $donation = $subscription->initialDonation();

        $stripePublishableKey = 'stripe-publishable-key';
        $stripeConnectedAccountId = 'stripe-connected-account-id';
        $stripePlan = Plan::constructFrom([
            'amount' => $subscription->amount->formatToMinorAmount(),
            'interval' => $subscription->period,
            'interval_count' => $subscription->frequency,
            'currency' => $subscription->amount->getCurrency(),
            'product' => Product::constructFrom([
                'name' => sprintf('%1$s - %2$s', $form->title, $donation->amount->formatToDecimal()),
                'statement_descriptor' => 'statement descriptor',
                'type' => 'service',
            ])
        ]);

        $stripeSubscription = \Stripe\Subscription::constructFrom([
            'items' => [
                [
                    'plan' => $stripePlan->id,
                ]
            ],
            'metadata' => give_stripe_prepare_metadata($donation->id),
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.payment_intent'],
            'latest_invoice' => [
                'payment_intent' => [
                    'client_secret' => 'stripe-client-secret',
                ]
            ]
        ]);

        $mockGateway = $this->getMockGateway([
            'getOrCreateStripeCustomerFromDonation',
            'createStripePlan',
            'createStripeSubscription',
            'updateSubscriptionMetaFromStripeSubscription',
            'updateSubscriptionInitialDonationMetaFromStripeSubscription',
        ]);

        $mockCustomer = Customer::constructFrom([
            'id' => 'stripe-customer-id',
            'name' => "$donation->firstName $donation->lastName",
            'email' => $donation->email,
            ['stripe_account' => $stripeConnectedAccountId]
        ]);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('setUpStripeAppInfo')
            ->with($donation->formId);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('getOrCreateStripeCustomerFromDonation')
            ->with($stripeConnectedAccountId, $donation)
            ->willReturn($mockCustomer);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('createStripePlan')
            ->with($subscription)
            ->willReturn($stripePlan);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('createStripeSubscription')
            ->with( $donation, $subscription, $mockCustomer, $stripePlan)
            ->willReturn($stripeSubscription);

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

        $response = $mockGateway->createSubscription($donation, $subscription, $gatewayData);

        $this->assertEquals(
            $response,
            new RespondToBrowser([
                'clientSecret' => $stripeSubscription->latest_invoice->payment_intent->client_secret,
                'returnUrl' => $gatewayData['successUrl'],
            ])
        );
    }

    /**
     * @since 0.3.0
     */
    protected function getMockGateway(array $methods = [])
    {
        return $this->createMock(
            StripePaymentElementGatewaySubscriptionModule::class,
            function (PHPUnit_Framework_MockObject_MockBuilder $mockBuilder) use ($methods) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(
                    array_merge(
                        [
                            'setUpStripeAppInfo',
                            'generateStripePaymentIntent',
                            'getStripePublishableKey',
                            'getStripeConnectedAccountKey'
                        ],
                        $methods
                    )
                );

                return $mockBuilder->getMock();
            }
        );
    }
}
