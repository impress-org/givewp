<?php

namespace Give\TestData\Framework\Provider;

abstract class RandomProvider implements ProviderContract {

	public function __construct( \Faker\Generator $faker ) {
		$this->faker = $faker;
	}

	abstract public function __invoke();
}
