<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Webhooks\EventHandlers\Actions;

use Exception;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class UpdateSubscriptionStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased   Remove the "$gatewaySubscriptionId" parameter from UpdateSubscriptionStatus action
     * @since        2.3.0
     *
     * @dataProvider subscriptionStatus
     *
     * @throws Exception
     */
    public function testShouldUpdateSubscriptionStatus(string $constant, SubscriptionStatus $status)
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::PENDING(),
            'gatewaySubscriptionId' => 'gateway-subscription-id',
        ]);

        (new UpdateSubscriptionStatus())($subscription, $status);

        // re-fetch subscription
        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->equals($status));
    }

    /**
     * @since 2.3.0
     */
    public function subscriptionStatus(): array
    {
        $values = [];
        foreach (SubscriptionStatus::values() as $key => $value) {
            $values[] = [$key, $value];
        }

        return $values;
    }
}
