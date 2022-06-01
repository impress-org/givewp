<?php

namespace Give\Subscriptions\Factories;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

class SubscriptionFactory extends ModelFactory
{

    /**
     * @since 2.20.0 update default donorId to create factory
     * @since 2.19.6
     *
     * @return array
     * @throws Exception
     */
    public function definition()
    {
        return [
            'amount' => new Money($this->faker->numberBetween(25, 50000), 'USD'),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => $this->faker->numberBetween(1, 4),
            'donorId' => Donor::factory()->create()->id,
            'installments' => $this->faker->numberBetween(0, 12),
            'feeAmountRecovered' => new Money(0, 'USD'),
            'status' => SubscriptionStatus::PENDING(),
            'donationFormId' => 1
        ];
    }

    /**
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function createRenewal($subscriptionId, array $attributes = [])
    {
        return Donation::factory()->create(
            array_merge([
                'status' => DonationStatus::RENEWAL(),
                'subscriptionId' => $subscriptionId,
                'parentId' => give()->subscriptions->getInitialDonationId($subscriptionId),
            ], $attributes)
        );
    }

    /**
     * @since 2.19.6
     *
     * @param $model
     * @return void
     * @throws Exception
     */
    public function afterCreating($model)
    {
        /** @var Subscription $subscription */
        $subscription = $model;

        // check if initial donation ID (parent_payment_id has been recorded
        $initialDonationId = give()->subscriptions->getInitialDonationId($subscription->id);

        // for backwards compatability update the subscription parent_payment_id column
        if (!$initialDonationId) {
            $donation = Donation::factory()->create(['donorId' => $subscription->donorId]);
            give()->donations->updateLegacyDonationMetaAsInitialSubscriptionDonation($donation->id);
            give()->subscriptions->updateLegacyColumns(
                $subscription->id,
                [
                    'parent_payment_id' => $donation->id,
                    'expiration' => $subscription->expiration()
                ]
            );
            $subscription->gatewayId = $donation->gatewayId;
        }
    }
}
