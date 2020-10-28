<?php

namespace Give\TestData;

abstract class ProviderFactory extends BaseFactory {

	protected $providers = [
		'currency'    => Provider\RandomCurrency::class,
		'amount'      => Provider\RandomAmount::class,
		'gateway'     => Provider\RandomGateway::class,
		'paymentMode' => Provider\RandomPaymentMode::class,
	];

	public function __construct() {
		parent::__construct();
		foreach ( $this->providers as $alias => $providerClass ) {
			$this->$alias = new $providerClass( $this->faker );
		}
	}

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
