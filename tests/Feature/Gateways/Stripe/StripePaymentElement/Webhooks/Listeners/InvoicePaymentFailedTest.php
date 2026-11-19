<?php

namespace Give\Tests\Feature\Gateways\Stripe\StripePaymentElement\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\InvoicePaymentFailed;
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

class InvoicePaymentFailedTest extends TestCase
{
    use HasMockStripeAccounts;
    use RefreshDatabase;

    /**
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function testShouldUpdateSubscriptionStatusToFailing()
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

        $invoice = Invoice::constructFrom([
            'id' => $donation->gatewayTransactionId,
            'total' => $donation->amount->formatToMinorAmount(),
            'currency' => $donation->amount->getCurrency()->getCode(),
            'createdAt' => $donation->createdAt->format('U'),
            'subscription' => $subscription->gatewaySubscriptionId,
            'payment_intent' => $donation->gatewayTransactionId,
            'attempted' => true,
            'paid' => false,
            'next_payment_attempt' => $donation->createdAt->format('U'),
        ]);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => $invoice
            ]
        ]);

        $listener = $this->createMockWithCallback(
            InvoicePaymentFailed::class,
            function (MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['triggerLegacyFailedEmailNotificationEvent']);

                return $mockBuilder->getMock();
            }
        );

        /** @var MockObject $listener */
        $listener->expects($this->once())
            ->method('triggerLegacyFailedEmailNotificationEvent')
            ->with($invoice);

        $listener->processEvent($mockEvent);

        // Refresh subscription model
        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isFailing());
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
            'gatewaySubscriptionId' => '',
            'gatewayId' => StripePaymentElementGateway::id(),
        ]);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => Invoice::constructFrom(array_merge([
                    'id' => 'stripe-invoice-id',
                    'attempted' => true,
                    'paid' => false,
                    'next_payment_attempt' => time(),
                ], $invoiceData))
            ]
        ]);

        $listener = $this->createMockWithCallback(
            InvoicePaymentFailed::class,
            function (MockBuilder $mockBuilder) {
                $mockBuilder->setMethods(['triggerLegacyFailedEmailNotificationEvent']);

                return $mockBuilder->getMock();
            }
        );

        /** @var MockObject $listener */
        $listener->expects($this->never())
            ->method('triggerLegacyFailedEmailNotificationEvent');

        $this->assertFalse($listener->processEvent($mockEvent));
        $this->assertTrue(Subscription::find($subscription->id)->status->isActive());
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

        $this->assertTrue(has_action('give_recurring_stripe_processing_invoice_payment_failed'));

        do_action('give_recurring_stripe_processing_invoice_payment_failed', $mockEvent);
    }
}
