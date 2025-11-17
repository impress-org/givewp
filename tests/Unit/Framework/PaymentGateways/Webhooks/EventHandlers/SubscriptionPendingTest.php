<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionPending;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.5.0
 */
class SubscriptionPendingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.5.0
     *
     * @throws Exception
     */
    public function testShouldSetStatusToPending()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::COMPLETED(),
            'gatewaySubscriptionId' => 'gateway-subscription-id',
        ]);

        give(SubscriptionPending::class)($subscription->gatewaySubscriptionId);

        $subscription = Subscription::find($subscription->id); // re-fetch subscription
        $this->assertTrue($subscription->status->isPending());
    }
}
