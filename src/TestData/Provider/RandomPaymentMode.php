<?php

namespace Give\TestData\Provider;

class RandomPaymentMode extends RandomProvider {

	public function __invoke() {
		return $this->faker->boolean( 80 ) ? 'live' : 'test';
	}
}
