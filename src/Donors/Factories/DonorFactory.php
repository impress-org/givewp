<?php

namespace Give\Donors\Factories;

use Give\Framework\Models\Factories\ModelFactory;

class DonorFactory extends ModelFactory
{
    /**
     * @since 2.19.6
     *
     * @return array
     */
    public function definition()
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        return [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'name' => trim("$firstName $lastName"),
            'email' => $this->faker->email
        ];
    }
}
