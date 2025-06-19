<?php

namespace Give\Donors\Factories;

use Give\Donors\ValueObjects\DonorAddress;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Support\ValueObjects\Money;

class DonorFactory extends ModelFactory
{
    /**
     * @since 4.4.0 Add "company", "avatarId", "additionalEmails", and "addresses" properties
     * @since 3.7.0 Add "phone" property
     * @since 2.19.6
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;

        // Generate 0-3 additional emails
        $additionalEmails = [];
        $additionalEmailCount = $this->faker->numberBetween(0, 3);
        for ($i = 0; $i < $additionalEmailCount; $i++) {
            $additionalEmails[] = $this->faker->unique()->email;
        }

        // Generate 0-3 addresses
        $addresses = [];
        $addressCount = $this->faker->numberBetween(0, 3);
        for ($i = 0; $i < $addressCount; $i++) {
            $addresses[] = DonorAddress::fromArray([
                'address1' => $this->faker->streetAddress,
                'address2' => $this->faker->optional(0.3)->secondaryAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->stateAbbr,
                'country' => $this->faker->countryCode,
                'zip' => $this->faker->postcode,
            ]);
        }

        $data = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'prefix' => $this->faker->randomElement(give_get_option('title_prefixes', array_values(give_get_default_title_prefixes()))),
            'name' => trim("$firstName $lastName"),
            'email' => $this->faker->email,
            'phone' => $this->faker->phoneNumber,
            'company' => $this->faker->company,
            'avatarId' => $this->faker->optional(0.2)->numberBetween(1, 9999),
            'additionalEmails' => $additionalEmails,
            'addresses' => $addresses,
            'totalAmountDonated' => new Money(0, 'USD'),
            'totalNumberOfDonations' => 0
        ];

        return $data;
    }
}
