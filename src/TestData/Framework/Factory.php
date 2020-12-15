<?php

namespace Give\TestData\Framework;

use Faker\Generator;

abstract class Factory implements FactoryContract {
	use ProviderForwarder;

	/** @var Generator */
	protected $faker;

	public function __construct( Generator $faker ) {
		$this->faker = $faker;
	}

	/**
	 * @param  int  $count
	 *
	 * @return \Generator
	 */
	public function make( $count ) {
		for ( $i = 0; $i < $count; $i ++ ) {
			yield $this->definition();
		}
	}

	/**
	 * Allways generate consistent data
	 *
	 * @param bool $consistent
	 *
	 * @return Factory
	 */
	public function consistent( $consistent = true ) {
		if ( $consistent ) {
			$this->faker->seed( mt_getrandmax() );
		}

		return $this;
	}

	abstract public function definition();
}
