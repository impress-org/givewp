<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionCancelled;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class SubscriptionCancelledTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
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
