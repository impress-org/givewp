<?php

namespace Give\Donors\Factories;

use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Support\ValueObjects\Money;

class DonorFactory extends ModelFactory
{
    /**
     * @since 2.19.6
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        return [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'name' => trim("$firstName $lastName"),
            'email' => $this->faker->email,
            'totalAmountDonated' => new Money(0, 'USD'),
            'totalNumberOfDonations' => 0
        ];
    }
}
