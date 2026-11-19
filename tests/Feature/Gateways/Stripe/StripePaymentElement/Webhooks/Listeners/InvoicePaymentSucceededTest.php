<?php

namespace Give\Tests\Feature\Gateways\Stripe\StripePaymentElement\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Decorators\SubscriptionModelDecorator;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\InvoicePaymentSucceeded;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\Feature\Gateways\Stripe\TestTraits\HasMockStripeAccounts;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Stripe\Event;
use Stripe\Invoice;

class InvoicePaymentSucceededTest extends TestCase
{
    use HasMockStripeAccounts;
    use RefreshDatabase;

    /**
     * @since 4.3.0 Update Stripe Invoice metadata
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function testShouldUpdateDonationStatusOfInitialDonation()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $subscription->gatewayId = StripePaymentElementGateway::id();
        $subscription->gatewaySubscriptionId = 'stripe-subscription-id';
        $subscription->save();

        $donation = $subscription->initialDonation();
        $donation->status = DonationStatus::PROCESSING();
        $donation->gatewayId = StripePaymentElementGateway::id();
        $donation->gatewayTransactionId = 'stripe-payment-intent-id';
        $donation->save();

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => Invoice::constructFrom([
                    'id' => $donation->gatewayTransactionId,
                    'total' => $donation->amount->formatToMinorAmount(),
                    'currency' => $donation->amount->getCurrency()->getCode(),
                    'createdAt' => $donation->createdAt->format('U'),
                    'subscription' => $subscription->gatewaySubscriptionId,
                    'payment_intent' => 'stripe-payment-intent-id',
                ])
            ]
        ]);

        $listener = $this->createMockWithCallback(
            InvoicePaymentSucceeded::class,
            function (MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['updateStripeInvoiceMetaData']);

                return $mockBuilder->getMock();
            }
        );

        /** @var MockObject $listener */
        $listener->expects($this->once())
            ->method('updateStripeInvoiceMetaData');

        $listener->processEvent($mockEvent);

        // Refresh donation model
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isComplete());
        $this->assertCount(1, $subscription->donations);
    }

    /**
     * @since TBD
     *
     * @dataProvider invoiceWithoutSubscriptionProvider
     *
     * @throws Exception
     */
    public function testShouldIgnoreInvoiceWithoutSubscription(array $invoiceData)
    {
        $this->addMockStripeAccounts();

        // A subscription saved without a gateway subscription ID must not be matched to the invoice.
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 0,
            'gatewaySubscriptionId' => '',
            'gatewayId' => StripePaymentElementGateway::id(),
        ]);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => Invoice::constructFrom(array_merge([
                    'id' => 'stripe-invoice-id',
                    'payment_intent' => 'new-stripe-payment-intent-id',
                ], $invoiceData))
            ]
        ]);

        $listener = $this->createMockWithCallback(
            InvoicePaymentSucceeded::class,
            function (MockBuilder $mockBuilder) {
                $mockBuilder->setMethods(['updateStripeInvoiceMetaData']);

                return $mockBuilder->getMock();
            }
        );

        /** @var MockObject $listener */
        $listener->expects($this->never())
            ->method('updateStripeInvoiceMetaData');

        $this->assertFalse($listener->processEvent($mockEvent));
        $this->assertCount(1, Subscription::find($subscription->id)->donations);
    }

    /**
     * @since TBD
     */
    public function invoiceWithoutSubscriptionProvider(): array
    {
        return [
            'subscription is null' => [['subscription' => null]],
            'subscription is an empty string' => [['subscription' => '']],
            'parent is null' => [['parent' => null]],
            'parent has no subscription details' => [
                ['parent' => ['type' => 'quote_details', 'subscription_details' => null]],
            ],
        ];
    }

    /**
     * @since TBD
     */
    public function testHookShouldNotFailForInvoiceWithoutSubscription()
    {
        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => Invoice::constructFrom([
                    'id' => 'stripe-invoice-id',
                    'subscription' => null,
                ])
            ]
        ]);

        $this->assertTrue(has_action('give_recurring_stripe_processing_invoice_payment_succeeded'));

        do_action('give_recurring_stripe_processing_invoice_payment_succeeded', $mockEvent);
    }

    /**
     * @since 4.3.0 Update Stripe Invoice metadata
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function testShouldCreateRenewal()
    {
        $this->addMockStripeAccounts();

        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 0,
            'frequency' => 1,
            'period' => SubscriptionPeriod::MONTH(),
            'gatewaySubscriptionId' => 'stripe-subscription-id',
            'gatewayId' => StripePaymentElementGateway::id(),
        ]);

        // refresh subscription model
        $subscription = Subscription::find($subscription->id);

        $donation = $subscription->initialDonation();
        $donation->gatewayTransactionId = 'stripe-payment-intent-id';
        $donation->save();

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => Invoice::constructFrom([
                    'id' => $donation->gatewayTransactionId,
                    'total' => $donation->amount->formatToMinorAmount(),
                    'currency' => $donation->amount->getCurrency()->getCode(),
                    'createdAt' => $donation->createdAt->format('U'),
                    'subscription' => $subscription->gatewaySubscriptionId,
                    'payment_intent' => 'new-stripe-payment-intent-id'
                ])
            ]
        ]);

        //$listener = new InvoicePaymentSucceeded();

        $listener = $this->createMockWithCallback(
            InvoicePaymentSucceeded::class,
            function (MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['updateStripeInvoiceMetaData']);

                return $mockBuilder->getMock();
            }
        );

        /** @var MockObject $listener */
        $listener->expects($this->once())
            ->method('updateStripeInvoiceMetaData');

        $listener->processEvent($mockEvent);

        // Refresh subscription model
        $subscription = Subscription::find($subscription->id);

        $this->assertCount(2, $subscription->donations);
        $this->assertTrue($subscription->status->isActive());
    }

    /**
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function testShouldCompleteSubscription()
    {
        $this->addMockStripeAccounts();

        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 1,
            'frequency' => 1,
            'period' => SubscriptionPeriod::MONTH(),
            'gatewaySubscriptionId' => 'stripe-subscription-id',
            'gatewayId' => StripePaymentElementGateway::id(),
        ]);

        $donation = $subscription->initialDonation();
        $donation->gatewayTransactionId = 'stripe-payment-intent-id';
        $donation->save();

        //refresh subscription model
        $subscription = Subscription::find($subscription->id);
        //refresh donation model
        $donation = Donation::find($donation->id);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => Invoice::constructFrom([
                    'id' => $donation->gatewayTransactionId,
                    'total' => $donation->amount->formatToMinorAmount(),
                    'currency' => $donation->amount->getCurrency()->getCode(),
                    'createdAt' => $donation->createdAt->format('U'),
                    'subscription' => $subscription->gatewaySubscriptionId,
                    'payment_intent' => 'new-stripe-payment-intent-id',
                ])
            ]
        ]);

        $subscriptionModelDecorator = new SubscriptionModelDecorator($subscription);

        $listener = $this->createMockWithCallback(
            InvoicePaymentSucceeded::class,
            function (MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['cancelSubscription']);

                return $mockBuilder->getMock();
            }
        );

        /** @var MockObject $listener */
        $listener->expects($this->once())
            ->method('cancelSubscription')
            ->with($subscriptionModelDecorator);

        $listener->processEvent($mockEvent);

        // Refresh subscription model
        $subscription = Subscription::find($subscription->id);

        $this->assertSame(1, $subscription->totalDonations());
        $this->assertTrue($subscription->status->isCompleted());
    }

    /**
     * @since 4.3.0 Update Stripe Invoice metadata
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function testShouldCreateRenewalAndCompleteSubscription()
    {
        $this->addMockStripeAccounts();

        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 2,
            'frequency' => 1,
            'period' => SubscriptionPeriod::MONTH(),
            'gatewaySubscriptionId' => 'stripe-subscription-id',
            'gatewayId' => StripePaymentElementGateway::id(),
        ]);

        $donation = $subscription->initialDonation();
        $donation->gatewayTransactionId = 'stripe-payment-intent-id';
        $donation->save();

        //refresh subscription model
        $subscription = Subscription::find($subscription->id);

        //refresh donation model
        $donation = Donation::find($donation->id);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => Invoice::constructFrom([
                    'id' => $donation->gatewayTransactionId,
                    'total' => $donation->amount->formatToMinorAmount(),
                    'currency' => $donation->amount->getCurrency()->getCode(),
                    'createdAt' => $donation->createdAt->format('U'),
                    'subscription' => $subscription->gatewaySubscriptionId,
                    'payment_intent' => 'new-stripe-payment-intent-id',
                ])
            ]
        ]);

        $listener = $this->createMockWithCallback(
            InvoicePaymentSucceeded::class,
            function (MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['cancelSubscription', 'updateStripeInvoiceMetaData']);

                return $mockBuilder->getMock();
            }
        );

        /** @var MockObject $listener */
        $listener->expects($this->once())
            ->method('cancelSubscription');

        /** @var MockObject $listener */
        $listener->expects($this->once())
            ->method('updateStripeInvoiceMetaData');

        $listener->processEvent($mockEvent);

        // Refresh subscription model
        $subscription = Subscription::find($subscription->id);

        $this->assertCount(2, $subscription->donations);
        $this->assertTrue($subscription->status->isCompleted());
    }
}
