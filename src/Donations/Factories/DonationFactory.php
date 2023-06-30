<?php

namespace Give\Donations\Factories;

use Exception;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

class DonationFactory extends ModelFactory
{

    /**
     * @since 2.22.0 add optional support for anonymous and company properties
     * @since 2.20.0 update default donorId to create factory
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'status' => DonationStatus::PENDING(),
            'gatewayId' => TestGateway::id(),
            'mode' => DonationMode::TEST(),
            'type' => DonationType::SINGLE(),
            'amount' => new Money($this->faker->numberBetween(50, 50000), 'USD'),
            'donorId' => Donor::factory()->create()->id,
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'email' => $this->faker->email,
            'formId' => 1,
            'formTitle' => 'Form Title',
            'anonymous' => $this->faker->optional(0.5, false)->boolean(true),
            'company' => $this->faker->optional()->company,
            'comment' => $this->faker->optional()->text,
        ];
    }
}
