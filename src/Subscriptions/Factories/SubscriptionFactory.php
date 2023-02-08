<?php

namespace Give\Subscriptions\Factories;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Actions\GenerateNextRenewalForSubscription;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

class SubscriptionFactory extends ModelFactory
{
    /**
     * @since 2.24.0 add mode property
     * @since 2.20.0 update default donorId to create factory
     * @since 2.19.6
     *
     * @return array
     * @throws Exception
     */
    public function definition(): array
    {
        $frequency = $this->faker->numberBetween(1, 4);

        return [
            'amount' => new Money($this->faker->numberBetween(25, 50000), 'USD'),
            'period' => SubscriptionPeriod::MONTH(),
            'gatewayId' => TestGateway::id(),
            'frequency' => $frequency,
            'donorId' => Donor::factory()->create()->id,
            'installments' => $this->faker->numberBetween(0, 12),
            'feeAmountRecovered' => new Money(0, 'USD'),
            'status' => SubscriptionStatus::PENDING(),
            'renewsAt' => give(GenerateNextRenewalForSubscription::class)(SubscriptionPeriod::MONTH(), $frequency),
            'donationFormId' => 1,
            'mode' => SubscriptionMode::TEST(),
        ];
    }

    /**
     * @since 2.23.0
     *
     * @return Subscription|Subscription[]
     * @throws Exception
     */
    public function createWithDonation(array $attributes = []): Subscription
    {
        $subscriptions = $this->create($attributes);

        if ( $this->count === 1 ) {
            $subscriptions = [$subscriptions];
        }

        foreach ($subscriptions as $subscription) {
            $donation = Donation::factory()->create([
                'amount' => $subscription->amount,
                'type' => DonationType::SUBSCRIPTION(),
                'status' => DonationStatus::COMPLETE(),
                'gatewayId' => $subscription->gatewayId,
                'subscriptionId' => $subscription->id,
            ]);

            give()->subscriptions->updateLegacyParentPaymentId($subscription->id, $donation->id);
        }

        return $this->count === 1 ? $subscriptions[0] : $subscriptions;
    }

    /**
     * @since 2.23.0 pass subscription model and update parentId property
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function createRenewal(Subscription $subscription, int $count = 1, array $attributes = [])
    {
        return Donation::factory()->count($count)->create(
            array_merge([
                'amount' => $subscription->amount,
                'status' => DonationStatus::COMPLETE(),
                'type' => DonationType::RENEWAL(),
                'subscriptionId' => $subscription->id,
            ], $attributes)
        );
    }
}
