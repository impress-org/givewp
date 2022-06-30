<?php

namespace Give\Donations\Factories;

use Give\Framework\Models\Factories\ModelFactory;

class DonationNoteFactory extends ModelFactory
{
    /**
     * @since 2.21.0
     *
     * @return array
     */
    public function definition()
    {
        return [
            'donationId' => 1,
            'content' => $this->faker->text
        ];
    }
}
