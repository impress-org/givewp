<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\EventHandlers\SubscriptionActive;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class SubscriptionActiveTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased Rename "setStatus" to "__invoke"
     * @since      2.3.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldSetStatusToActive()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::PENDING(),
            'gatewaySubscriptionId' => 'gateway-subscription-id',
        ]);

        $subscriptionInitialDonation = $subscription->initialDonation();
        $subscriptionInitialDonation->status = DonationStatus::COMPLETE();
        $subscriptionInitialDonation->gatewayTransactionId = 'gateway-transaction-id';
        $subscriptionInitialDonation->save();

        give(SubscriptionActive::class)($subscription->gatewaySubscriptionId);

        $subscription = Subscription::find($subscription->id); // re-fetch subscription
        $this->assertTrue($subscription->status->isActive());
    }
}
