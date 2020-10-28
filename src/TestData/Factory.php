<?php

namespace Give\TestData;

use Faker\Generator;

abstract class Factory {

	/** @var \Faker\Generator */
	protected $faker;

	protected $providers = [
		'currency'    => Provider\RandomCurrency::class,
		'amount'      => Provider\RandomAmount::class,
		'gateway'     => Provider\RandomGateway::class,
		'paymentMode' => Provider\RandomPaymentMode::class,
	];

	public function __construct() {
		$this->faker = \Faker\Factory::create();
		foreach ( $this->providers as $alias => $providerClass ) {
			$this->$alias = new $providerClass( $this->faker );
		}
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

	/**
	 * Forward provider calls
	 *
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call( $name, $arguments ) {
		if ( property_exists( $this, $name ) ) {
			return call_user_func_array( $this->$name, $arguments );
		}
	}
}
