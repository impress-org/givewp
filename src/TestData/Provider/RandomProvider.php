<?php

namespace Give\TestData\Provider;

use Give\TestData\Contract\Provider;

abstract class RandomProvider implements Provider {

	public function __construct( \Faker\Generator $faker ) {
		$this->faker = $faker;
	}

	public function __invoke() {}
}
