<?php

namespace Give\Subscriptions\Factories;

use Give\Framework\Models\Factories\ModelFactory;

/**
 * @unreleased
 */
class SubscriptionNoteFactory extends ModelFactory
{
    /**
     * @unreleased
     */
    public function definition(): array
    {
        return [
            'subscriptionId' => 1,
            'content' => $this->faker->text,
        ];
    }
}
