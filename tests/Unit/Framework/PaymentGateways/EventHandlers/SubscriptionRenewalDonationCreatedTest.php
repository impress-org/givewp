<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\EventHandlers\SubscriptionRenewalDonationCreated;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class SubscriptionRenewalDonationCreatedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldCreateRenewalDonation()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $subscription->status = SubscriptionStatus::PENDING();
        $subscription->gatewaySubscriptionId = 'gateway-subscription-id';
        $subscription->save();

        $donation->status = DonationStatus::PENDING();
        $donation->gatewayTransactionId = 'gateway-transaction-id';
        $donation->save();

        $renewalGatewayTransactionId = 'renewal-gateway-transaction-id';

        give(SubscriptionRenewalDonationCreated::class)($subscription->gatewaySubscriptionId,
            $renewalGatewayTransactionId);

        $renewalDonation = give()->donations->getByGatewayTransactionId($renewalGatewayTransactionId);

        $this->assertEquals($subscription->id, $renewalDonation->subscriptionId);
    }
}
