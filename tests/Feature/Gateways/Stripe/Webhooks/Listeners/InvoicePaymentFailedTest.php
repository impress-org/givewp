<?php

namespace Give\Tests\Feature\Gateways\Stripe\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\NextGenStripeGateway;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\Webhooks\Listeners\InvoicePaymentFailed;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\Feature\Gateways\Stripe\TestTraits\HasMockStripeAccounts;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use Stripe\Event;
use Stripe\Invoice;

class InvoicePaymentFailedTest extends TestCase
{
    use RefreshDatabase, HasMockStripeAccounts;

    /**
     * @unreleased
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
            'gatewayId' => NextGenStripeGateway::id(),
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

        $listener = $this->createMock(
            InvoicePaymentFailed::class,
            function (PHPUnit_Framework_MockObject_MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['triggerLegacyFailedEmailNotificationEvent']);

                return $mockBuilder->getMock();
            }
        );

        /** @var PHPUnit_Framework_MockObject_MockObject $listener */
        $listener->expects($this->once())
            ->method('triggerLegacyFailedEmailNotificationEvent')
            ->with($invoice);

        $listener->processEvent($mockEvent);

        // Refresh subscription model
        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isFailing());
    }
}