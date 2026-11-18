<?php

namespace Give\Tests\Feature\Gateways\Stripe\StripePaymentElement\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\InvoicePaymentFailed;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\Feature\Gateways\Stripe\TestTraits\HasMockStripeAccounts;
use Give\Tests\Feature\Gateways\Stripe\TestTraits\HasStripePaymentElementSubscriptions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Stripe\Event;
use Stripe\Invoice;

class InvoicePaymentFailedTest extends TestCase
{
    use HasMockStripeAccounts;
    use HasStripePaymentElementSubscriptions;
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
            'billing_reason' => 'subscription_cycle',
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
     * @throws Exception
     */
    public function testShouldFailInitialDonationAndKeepSubscriptionPendingWhenFirstInvoiceFails()
    {
        $subscription = $this->createStripePaymentElementSubscription(
            SubscriptionStatus::PENDING(),
            DonationStatus::PROCESSING()
        );
        $invoice = $this->createFirstInvoice($subscription, 'open');

        $listener = $this->createListenerReturningInvoiceFromStripe($invoice);

        $listener->expects($this->never())
            ->method('triggerLegacyFailedEmailNotificationEvent');

        $listener->processEvent(Event::constructFrom(['data' => ['object' => $invoice]]));

        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->initialDonation()->status->isFailed());
        $this->assertTrue($subscription->status->isPending());
    }

    /**
     * Give Recurring sets the subscription to active when it creates it, before the browser
     * confirms the first payment, so it is still active when the first invoice fails.
     *
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldMoveActiveSubscriptionToPendingWhenFirstInvoiceFails()
    {
        $subscription = $this->createStripePaymentElementSubscription(
            SubscriptionStatus::ACTIVE(),
            DonationStatus::PROCESSING()
        );
        $invoice = $this->createFirstInvoice($subscription, 'open');

        $listener = $this->createListenerReturningInvoiceFromStripe($invoice);

        $listener->processEvent(Event::constructFrom(['data' => ['object' => $invoice]]));

        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->initialDonation()->status->isFailed());
        $this->assertTrue($subscription->status->isPending());
    }

    /**
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldNotFailInitialDonationWhenStripeReportsFirstInvoicePaid()
    {
        $subscription = $this->createStripePaymentElementSubscription(
            SubscriptionStatus::PENDING(),
            DonationStatus::PROCESSING()
        );
        $payloadInvoice = $this->createFirstInvoice($subscription, 'open');

        $listener = $this->createListenerReturningInvoiceFromStripe(
            $this->createFirstInvoice($subscription, 'paid')
        );

        $listener->processEvent(Event::constructFrom(['data' => ['object' => $payloadInvoice]]));

        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->initialDonation()->status->isProcessing());
        $this->assertTrue($subscription->status->isPending());
    }

    /**
     * @since TBD
     *
     * @param string $status The Stripe invoice status: open while unpaid, paid once paid.
     */
    private function createFirstInvoice(Subscription $subscription, string $status): Invoice
    {
        $donation = $subscription->initialDonation();

        return Invoice::constructFrom([
            'id' => 'stripe-invoice-id',
            'total' => $donation->amount->formatToMinorAmount(),
            'currency' => $donation->amount->getCurrency()->getCode(),
            'subscription' => $subscription->gatewaySubscriptionId,
            'payment_intent' => $donation->gatewayTransactionId,
            'billing_reason' => 'subscription_create',
            'status' => $status,
            'attempted' => true,
            'next_payment_attempt' => null,
        ]);
    }

    /**
     * @since TBD
     *
     * @param Invoice $invoiceFromStripe The invoice getCompleteInvoiceFromStripe() returns.
     *
     * @return InvoicePaymentFailed&MockObject
     */
    private function createListenerReturningInvoiceFromStripe(Invoice $invoiceFromStripe): InvoicePaymentFailed
    {
        $listener = $this->createMockWithCallback(
            InvoicePaymentFailed::class,
            function (MockBuilder $mockBuilder) {
                $mockBuilder->setMethods(['getCompleteInvoiceFromStripe', 'triggerLegacyFailedEmailNotificationEvent']);
            }
        );

        $listener->method('getCompleteInvoiceFromStripe')->willReturn($invoiceFromStripe);

        return $listener;
    }
}
