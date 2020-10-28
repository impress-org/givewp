<?php

namespace Give\TestData;

class DonationFactory extends Factory {

	public function definition() {
		return [
			'firstName'   => $this->faker->firstname,
			'lastName'    => $this->faker->lastName,
			'email'       => $this->faker->safeEmail,
			'amount'      => $this->amount(),
			'currency'    => $this->currency(),
			'gateway'     => $this->gateway(),
			'mode'        => $this->paymentMode(),
			'purchaseKey' => $this->faker->md5(),
			'dateCreated' => $this->faker->dateTimeThisYear()->format( 'Y-m-d H:i:s' ),
		];
	}
}
