<?php

namespace Give\TestData;

class RevenueFactory {

	use ProviderForwarder;

	/** @var \Faker\Generator */
	protected $faker;

	public function __construct( \Faker\Generator $faker ) {
		$this->faker = $faker;
	}

	public function make( $count ) {
		return array_map(
			function() {
				return $this->definition();
			},
			range( 1, $count )
		);
	}

	public function definition() {
		return [
			'firstName'   => $this->faker->firstname,
			'lastName'    => $this->faker->lastName,
			'email'       => $this->faker->safeEmail,
			'amount'      => $this->randomAmount(),
			'currency'    => 'USD', // Set a base currency and delegate multi-currency to Currency Switcher.
			'gateway'     => $this->randomGateway(),
			'mode'        => $this->randomPaymentMode(),
			'purchaseKey' => $this->faker->md5(),
			'dateCreated' => $this->faker->dateTimeThisYear()->format( 'Y-m-d H:i:s' ),
		];
	}
}
