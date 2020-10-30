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
		for ( $i = 0; $i < $count; $i++ ) {
			yield $this->definition();
		}
	}

	abstract public function definition();
}
