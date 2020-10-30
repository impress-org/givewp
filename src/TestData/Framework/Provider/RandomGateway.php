<?php

namespace Give\TestData\Framework\Provider;

class RandomGateway extends RandomProvider {

	/** @var array [ gatewaySlug, ... ] */
	protected $gateways = [
		'paypal',
		'stripe',
		'manual',
	];

	public function __invoke() {
		$count = count( $this->gateways );
		$index = $this->faker->biasedNumberBetween( 0, $count - 1, $function = 'sqrt' );
		return $this->gateways[ $index ];
	}
}
