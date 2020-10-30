<?php

namespace Give\TestData\Framework;

abstract class Factory implements FactoryContract {
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

	abstract public function definition();
}
