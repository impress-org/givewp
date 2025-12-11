<?php

namespace Give\Donors\Factories;

use Give\Framework\Models\Factories\ModelFactory;

/**
 * @since 4.4.0
 */
class DonorNoteFactory extends ModelFactory
{
    /**
     * @since 4.4.0
     */
    public function definition(): array
    {
        return [
            'donorId' => 1,
            'content' => $this->faker->text,
        ];
    }
}
