<?php

namespace Give\Tests\Feature\Gateways\Stripe\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\NextGenStripeGateway;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\Webhooks\Listeners\CustomerSubscriptionDeleted;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\Feature\Gateways\Stripe\TestTraits\HasMockStripeAccounts;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Stripe\Event;

class CustomerSubscriptionDeletedTest extends TestCase
{
    use RefreshDatabase, HasMockStripeAccounts;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldUpdateSubscriptionStatusToCompleted()
    {
        $this->addMockStripeAccounts();

        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 0,
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

        $stripeSubscription = \Stripe\Subscription::constructFrom([
            'id' => $subscription->gatewaySubscriptionId,
        ]);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => $stripeSubscription
            ]
        ]);

        $listener = new CustomerSubscriptionDeleted();

        $listener->processEvent($mockEvent);

        // Refresh subscription model
        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isCompleted());
    }
}