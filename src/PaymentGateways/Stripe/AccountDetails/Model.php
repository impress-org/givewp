<?php

namespace Give\PaymentGateways\Stripe\AccountDetails;

use InvalidArgumentException;
use Give\Helpers\ArrayDataSet;

class Model {

	/**
	 * @var array
	 */
	public $args = [];

	/**
	 * @param $args
	 */
	public function __construct( $args ) {
		$this->args = ArrayDataSet::camelCaseKeys( $args );
	}

	/**
	 * @param $key
	 * @throws InvalidArgumentException
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( ! isset( $this->args[ $key ] ) ) {
			throw new InvalidArgumentException( "The property `$key` does not exist." );
		}
		return $this->args[ $key ];
	}
}
