<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\EventHandlers\SubscriptionActive;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class SubscriptionActiveTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldSetStatusToActive()
    {
        $subscription = $this->getSubscriptionWithPendingInitialDonation();

        give(SubscriptionActive::class)($subscription->gatewaySubscriptionId);

        $subscription = Subscription::find($subscription->id); // re-fetch subscription

        $this->assertTrue($subscription->status->isActive());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldNotSetStatusToActive()
    {
        $subscription = $this->getSubscriptionWithPendingInitialDonation();

        give(SubscriptionActive::class)($subscription->gatewaySubscriptionId, 'test', true);

        $subscription = Subscription::find($subscription->id); // re-fetch subscription

        $this->assertNotTrue($subscription->status->isActive());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    private function getSubscriptionWithPendingInitialDonation(): Subscription
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::PENDING(),
            'gatewaySubscriptionId' => 'gateway-subscription-id',
        ]);

        $subscriptionInitialDonation = $subscription->initialDonation();
        $subscriptionInitialDonation->status = DonationStatus::PENDING();
        $subscriptionInitialDonation->gatewayTransactionId = 'gateway-transaction-id';
        $subscriptionInitialDonation->save();

        return $subscription;
    }
}
