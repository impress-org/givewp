<?php

namespace Give\Donors\Factories;

use Give\Framework\Models\Factories\ModelFactory;

/**
 * @unreleased
 */
class DonorNoteFactory extends ModelFactory
{
    /**
     * @unreleased
     */
    public function definition(): array
    {
        return [
            'donorId' => 1,
            'content' => $this->faker->text,
        ];
    }
}
