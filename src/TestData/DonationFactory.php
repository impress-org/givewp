<?php

namespace Give\TestData;

class DonationFactory extends Framework\Factory {

	public function definition() {
		return [
			'donor_id'             => $this->randomDonor(),
			'payment_total'        => $this->randomAmount(),
			'payment_currency'     => 'USD', // Set a base currency and delegate multi-currency to Currency Switcher.
			'payment_gateway'      => $this->randomGateway(),
			'payment_mode'         => $this->randomPaymentMode(),
			'payment_purchase_key' => $this->faker->md5(),
			'completed_date'       => $this->faker->dateTimeThisYear()->format( 'Y-m-d H:i:s' ),
		];
	}
}
