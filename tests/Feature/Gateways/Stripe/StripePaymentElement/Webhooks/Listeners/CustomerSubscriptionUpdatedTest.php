<?php

namespace Give\Tests\Feature\Gateways\Stripe\StripePaymentElement\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\CustomerSubscriptionUpdated;
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
use Stripe\Subscription as StripeSubscription;

/**
 * @since 4.16.7
 */
class CustomerSubscriptionUpdatedTest extends TestCase
{
    use RefreshDatabase;
    use HasMockStripeAccounts;
    use HasStripePaymentElementSubscriptions;

    /**
     * @since 4.16.7
     *
     * @throws Exception
     */
    public function testShouldUpdateFailingSubscriptionStatusToActiveWhenStripeReportsActive()
    {
        $this->addMockStripeAccounts();

        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::FAILING(),
            'installments' => 0,
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

        $stripeSubscription = \Stripe\Subscription::constructFrom([
            'id' => $subscription->gatewaySubscriptionId,
            'status' => 'active',
        ]);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => $stripeSubscription
            ]
        ]);

        $listener = new CustomerSubscriptionUpdated();

        $listener->processEvent($mockEvent);

        // Refresh subscription model
        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isActive());
    }

    /**
     * @since 4.16.7
     *
     * @throws Exception
     */
    public function testShouldUpdatePausedSubscriptionStatusToActiveWhenStripeReportsActive()
    {
        $this->addMockStripeAccounts();

        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::PAUSED(),
            'installments' => 0,
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

        $stripeSubscription = \Stripe\Subscription::constructFrom([
            'id' => $subscription->gatewaySubscriptionId,
            'status' => 'active',
        ]);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => $stripeSubscription
            ]
        ]);

        $listener = new CustomerSubscriptionUpdated();

        $listener->processEvent($mockEvent);

        // Refresh subscription model
        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isActive());
    }

    /**
     * @since 4.16.7
     *
     * @throws Exception
     */
    public function testShouldNotUpdateFailingSubscriptionWhenStripeReportsNonActive()
    {
        $this->addMockStripeAccounts();

        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::FAILING(),
            'installments' => 0,
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

        $stripeSubscription = \Stripe\Subscription::constructFrom([
            'id' => $subscription->gatewaySubscriptionId,
            'status' => 'past_due',
        ]);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => $stripeSubscription
            ]
        ]);

        $listener = new CustomerSubscriptionUpdated();

        $listener->processEvent($mockEvent);

        // Refresh subscription model
        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isFailing());
    }

    /**
     * @since 4.16.7
     *
     * @throws Exception
     */
    public function testShouldNotProcessNonStripePaymentElementSubscriptions()
    {
        $this->addMockStripeAccounts();

        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::FAILING(),
            'installments' => 0,
            'frequency' => 1,
            'period' => SubscriptionPeriod::MONTH(),
            'gatewaySubscriptionId' => 'stripe-subscription-id',
            'gatewayId' => 'other_gateway',
        ]);

        $donation = $subscription->initialDonation();
        $donation->gatewayTransactionId = 'stripe-payment-intent-id';
        $donation->save();

        //refresh subscription model
        $subscription = Subscription::find($subscription->id);
        //refresh donation model
        $donation = Donation::find($donation->id);

        $stripeSubscription = \Stripe\Subscription::constructFrom([
            'id' => $subscription->gatewaySubscriptionId,
            'status' => 'active',
        ]);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => $stripeSubscription
            ]
        ]);

        $listener = new CustomerSubscriptionUpdated();

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
    public function testShouldUpdatePendingSubscriptionStatusToActiveWhenStripeReportsActive()
    {
        $subscription = $this->createStripePaymentElementSubscription(
            SubscriptionStatus::PENDING(),
            DonationStatus::PROCESSING()
        );

        (new CustomerSubscriptionUpdated())->processEvent(
            $this->createSubscriptionUpdatedEvent($subscription, 'active')
        );

        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isActive());
    }

    /**
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldCancelSubscriptionAndFailInitialDonationWhenStripeExpiresIt()
    {
        $subscription = $this->createStripePaymentElementSubscription(
            SubscriptionStatus::PENDING(),
            DonationStatus::PROCESSING()
        );

        $listener = $this->createListenerReturningSubscriptionFromStripe(
            StripeSubscription::constructFrom([
                'id' => $subscription->gatewaySubscriptionId,
                'status' => 'incomplete_expired',
            ])
        );

        $listener->processEvent($this->createSubscriptionUpdatedEvent($subscription, 'incomplete_expired'));

        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isCancelled());
        $this->assertTrue($subscription->initialDonation()->status->isFailed());
    }

    /**
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldNotCancelSubscriptionWhenStripeDoesNotConfirmItExpired()
    {
        $subscription = $this->createStripePaymentElementSubscription(
            SubscriptionStatus::PENDING(),
            DonationStatus::PROCESSING()
        );

        $listener = $this->createListenerReturningSubscriptionFromStripe(
            StripeSubscription::constructFrom([
                'id' => $subscription->gatewaySubscriptionId,
                'status' => 'incomplete',
            ])
        );

        $listener->processEvent($this->createSubscriptionUpdatedEvent($subscription, 'incomplete_expired'));

        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isPending());
        $this->assertTrue($subscription->initialDonation()->status->isProcessing());
    }

    /**
     * @since TBD
     *
     * @param string $stripeStatus The Stripe subscription status the payload reports.
     */
    private function createSubscriptionUpdatedEvent(Subscription $subscription, string $stripeStatus): Event
    {
        return Event::constructFrom([
            'data' => [
                'object' => StripeSubscription::constructFrom([
                    'id' => $subscription->gatewaySubscriptionId,
                    'status' => $stripeStatus,
                ]),
            ],
        ]);
    }

    /**
     * @since TBD
     *
     * @param StripeSubscription $subscriptionFromStripe The subscription getStripeSubscriptionFromStripe() returns.
     *
     * @return CustomerSubscriptionUpdated&MockObject
     */
    private function createListenerReturningSubscriptionFromStripe(
        StripeSubscription $subscriptionFromStripe
    ): CustomerSubscriptionUpdated {
        $listener = $this->createMockWithCallback(
            CustomerSubscriptionUpdated::class,
            function (MockBuilder $mockBuilder) {
                $mockBuilder->setMethods(['getStripeSubscriptionFromStripe']);
            }
        );

        $listener->method('getStripeSubscriptionFromStripe')->willReturn($subscriptionFromStripe);

        return $listener;
    }
}
