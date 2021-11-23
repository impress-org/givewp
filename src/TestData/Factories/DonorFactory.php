<?php

namespace Give\TestData\Factories;

use Give\TestData\Framework\Factory;

/**
 * Class DonorFactory
 * @package Give\TestData\Factories
 */
class DonorFactory extends Factory
{

    /**
     * Donor definition
     *
     * @since 1.0.0
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'date_created' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
        ];
    }
}
