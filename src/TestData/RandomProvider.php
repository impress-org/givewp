<?php

namespace Give\TestData;

abstract class RandomProvider implements Contract\Provider {

	public function __construct( \Faker\Generator $faker ) {
		$this->faker = $faker;
	}

	public function __invoke() {}
}
