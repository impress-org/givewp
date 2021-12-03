<?php

namespace Give\TestData\Factories;

use Give\TestData\Framework\Factory;
use Give\ValueObjects\Money;

class RevenueFactory extends Factory
{
    public function definition()
    {
        $donationForm = $this->randomDonationForm();

        return [
            'donation_id' => $this->randomDonation(),
            'form_id' => $donationForm['id'],
            'amount' => Money::of($this->randomAmount(), give_get_option('currency'))->getMinorAmount(),
        ];
    }
}
