<?php

namespace Give\TestData\Framework\Provider;

class RandomCurrency extends RandomProvider {

	/** @var array [currencyCode, ... ] */
	protected $currencies = [
		'EUR',
		'CAD',
		'USD',
	];

	public function __invoke() {
		$count = count( $this->currencies );
		$index = $this->faker->biasedNumberBetween( 0, $count - 1, $function = 'sqrt' );

		return $this->currencies[ $index ];
	}
}
