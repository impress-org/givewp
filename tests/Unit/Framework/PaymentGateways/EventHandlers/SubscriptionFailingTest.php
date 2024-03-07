<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\EventHandlers\SubscriptionFailing;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class SubscriptionFailingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased Rename "setStatus" to "__invoke"
     * @since      2.3.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldSetStatusToFailing()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::PENDING(),
            'gatewaySubscriptionId' => 'gateway-subscription-id',
        ]);

        give(SubscriptionFailing::class)($subscription->gatewaySubscriptionId);

        $subscription = Subscription::find($subscription->id); // re-fetch subscription
        $this->assertTrue($subscription->status->isFailing());
    }
}
