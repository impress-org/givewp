<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\EventHandlers\SubscriptionCancelled;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class SubscriptionCancelledTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased Rename "setStatus" to "__invoke"
     * @since      2.3.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldSetStatusToCancelled()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::PENDING(),
            'gatewaySubscriptionId' => 'gateway-subscription-id',
        ]);

        give(SubscriptionCancelled::class)($subscription->gatewaySubscriptionId);

        $subscription = Subscription::find($subscription->id); // re-fetch subscription
        $this->assertTrue($subscription->status->isCancelled());
    }
}
