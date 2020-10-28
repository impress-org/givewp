<?php

namespace Give\TestData\Provider;

use Give\TestData\RandomProvider;

class RandomPaymentMode extends RandomProvider {

	public function __invoke() {
		return $this->faker->boolean( 80 ) ? 'live' : 'test';
	}
}
