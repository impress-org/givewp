<?php

namespace Give\Subscriptions\Factories;

use Give\Framework\Models\Factories\ModelFactory;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

class SubscriptionFactory extends ModelFactory {

    /**
     * @return array
     */
    public function definition()
    {
        return [
          'amount' => $this->faker->numberBetween(25, 50000),
          'period' => SubscriptionPeriod::MONTH(),
          'frequency' => $this->faker->numberBetween(1, 4),
          'donorId' => 1,
          'installments' => $this->faker->numberBetween(0, 12),
          'feeAmount' => 0,
          'status' => SubscriptionStatus::PENDING(),
          'donationFormId' => 1
      ];
    }
}
