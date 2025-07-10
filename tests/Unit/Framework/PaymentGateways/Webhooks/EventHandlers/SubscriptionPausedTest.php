<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionPaused;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.5.0
 */
class SubscriptionPausedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.5.0
     *
     * @throws Exception
     */
    public function testShouldSetStatusToPaused()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::PENDING(),
            'gatewaySubscriptionId' => 'gateway-subscription-id',
        ]);

        give(SubscriptionPaused::class)($subscription->gatewaySubscriptionId);

        $subscription = Subscription::find($subscription->id); // re-fetch subscription
        $this->assertTrue($subscription->status->isPaused());
    }
}
