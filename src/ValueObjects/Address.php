<?php

namespace Give\ValueObjects;

use InvalidArgumentException;

/**
 * Class Address.
 */
class Address implements ValueObjects {

	/**
	 * @var string
	 */
	public $line1;

	/**
	 * @var string
	 */
	public $line2;

	/**
	 * @var string
	 */
	public $city;

	/**
	 * @var string
	 */
	public $state;

	/**
	 * @var string
	 */
	public $postalCode;

	/**
	 * @var string
	 */
	public $country;


	/**
	 * Take array and return object.
	 *
	 * @param array $array
	 *
	 * @return Address
	 * @since 2.7.0
	 */
	public static function fromArray( $array ) {
		$expectedKeys = [ 'line1', 'line2', 'city', 'state', 'postalCode', 'country' ];

		$array = array_intersect_key( $array, array_flip( $expectedKeys ) );

		if ( empty( $array ) ) {
			throw new InvalidArgumentException(
				'Invalid Address object, must have the exact following keys: ' . implode( ', ', $expectedKeys )
			);
		}

		$address = new self();
		foreach ( $array as $key => $value ) {
			$address->$key = $value;
		}

		return $address;
	}
}
