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
     * @unreleased
     *
     * @throws Exception
     */
    public function beforeMaking($attributes)
    {
        if (!$attributes['donorId'] && !Donor::find(1)) {
            Donor::factory()->create();
        }
    }

    /**
     * @since 2.19.6
     *
     * @return array
     */
    public function definition()
    {
        return [
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'mode' => DonationMode::TEST(),
            'amount' => $this->faker->numberBetween(50, 50000),
            'currency' => 'USD',
            'donorId' => 1,
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'email' => $this->faker->email,
            'formId' => 1,
            'formTitle' => 'Form Title',
        ];
    }
}
