<?php

namespace Give\Donations\Factories;

use Exception;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Models\Factories\ModelFactory;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

class DonationFactory extends ModelFactory
{

    /**
     * @unreleased update default donorId to create factory
     * @since 2.19.6
     *
     * @return array
     * @throws Exception
     */
    public function definition()
    {
        return [
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'mode' => DonationMode::TEST(),
            'amount' => $this->faker->numberBetween(50, 50000),
            'currency' => 'USD',
            'donorId' => Donor::factory()->create()->id,
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'email' => $this->faker->email,
            'formId' => 1,
            'formTitle' => 'Form Title',
        ];
    }
}
