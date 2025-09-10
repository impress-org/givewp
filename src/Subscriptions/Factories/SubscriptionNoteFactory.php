<?php

namespace Give\Subscriptions\Factories;

use Give\Framework\Models\Factories\ModelFactory;

/**
 * @since 4.8.0
 */
class SubscriptionNoteFactory extends ModelFactory
{
    /**
     * @since 4.8.0
     */
    public function definition(): array
    {
        return [
            'subscriptionId' => 1,
            'content' => $this->faker->text,
        ];
    }
}
