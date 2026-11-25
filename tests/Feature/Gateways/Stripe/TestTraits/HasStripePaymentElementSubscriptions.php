<?php

namespace Give\Tests\Feature\Gateways\Stripe\TestTraits;

use Exception;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * Requires HasMockStripeAccounts: creating a Stripe Payment Element donation reads the Stripe
 * accounts option.
 *
 * @since 4.20.0
 */
trait HasStripePaymentElementSubscriptions
{
    /**
     * Create a monthly Stripe Payment Element subscription with its first donation.
     *
     * @since 4.20.0
     *
     * @param SubscriptionStatus $subscriptionStatus The GiveWP subscription status.
     * @param DonationStatus $initialDonationStatus The status of the first donation.
     *
     * @return Subscription The subscription, read back from the database.
     * @throws Exception
     */
    public function createStripePaymentElementSubscription(
        SubscriptionStatus $subscriptionStatus,
        DonationStatus $initialDonationStatus
    ): Subscription {
        $this->addMockStripeAccounts();

        $subscription = Subscription::factory()->createWithDonation(
            [
                'status' => $subscriptionStatus,
                'installments' => 0,
                'frequency' => 1,
                'period' => SubscriptionPeriod::MONTH(),
                'gatewaySubscriptionId' => 'stripe-subscription-id',
                'gatewayId' => StripePaymentElementGateway::id(),
            ],
            [
                'status' => $initialDonationStatus,
                'gatewayTransactionId' => 'stripe-payment-intent-id',
            ]
        );

        return Subscription::find($subscription->id);
    }
}
