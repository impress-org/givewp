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
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testShouldCancelSubscriptionAndFailInitialDonationWhenStripeExpiresIt()
    {
        $subscription = $this->createStripePaymentElementSubscription(
            SubscriptionStatus::PENDING(),
            DonationStatus::PROCESSING()
        );

        $event = Event::constructFrom([
            'data' => [
                'object' => StripeSubscription::constructFrom([
                    'id' => $subscription->gatewaySubscriptionId,
                    'status' => 'incomplete_expired',
                ]),
            ],
        ]);

        (new CustomerSubscriptionUpdated())->processEvent($event);

        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isCancelled());
        $this->assertTrue($subscription->initialDonation()->status->isFailed());
    }
}
