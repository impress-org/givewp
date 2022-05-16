<?php

namespace Give\Donations\Factories;

use Give\Framework\Models\Factories\ModelFactory;

class DonationNoteFactory extends ModelFactory
{
    /**
     * @unreleased
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
