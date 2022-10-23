<?php

namespace Give\Donations\Factories;

use Give\Framework\Models\Factories\ModelFactory;

class DonationNoteFactory extends ModelFactory
{
    /**
     * @since 2.23.0 add array return type
     * @since 2.21.0
     */
    public function definition(): array
    {
        return [
            'donationId' => 1,
            'content' => $this->faker->text
        ];
    }
}
