<?php

namespace Give\TestData\Framework\Provider;

class RandomGoal extends RandomProvider {

	/** @var array [ int, ... ] */
	protected $goals = [
		1000,
		2500,
		5000,
		10000,
		25000,
	];

	public function __invoke() {
		return $this->faker->randomElement( $this->goals );
	}
}
