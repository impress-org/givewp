<?php

namespace Give\TestData;

use Faker\Generator;

abstract class BaseFactory implements Contract\Factory {

	/** @var \Faker\Generator */
	protected $faker;

	public function __construct() {
		$this->faker = \Faker\Factory::create();
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
