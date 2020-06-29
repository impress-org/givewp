<?php

namespace Give\ValueObjects;

use Give\Helpers\ArrayDataSet;
use InvalidArgumentException;

/**
 * Class CardInfo
 *
 * @package Give\ValueObjects
 *
 * @since 2.7.0
 */
class CardInfo implements ValueObjects {
	/**
	 * Cardholder name.
	 *
	 * @var  string
	 */
	public $name;

	/**
	 * Cardholder name.
	 *
	 * @var  string
	 */
	public $number;

	/**
	 * Card security pin.
	 *
	 * @var string
	 */
	public $cvc;

	/**
	 * Card expire month
	 *
	 * @var string
	 */
	public $expMonth;

	/**
	 * Card expire year.
	 *
	 * @var string
	 */
	public $expYear;

	/**
	 * Address.
	 *
	 * @var Address
	 */
	public $address;


	/**
	 * Take array and return object.
	 *
	 * @param $array
	 *
	 * @return CardInfo
	 */
	public static function fromArray( $array ) {
		$expectedKeys = [ 'name', 'cvc', 'expMonth', 'expYear', 'number', 'address' ];

		$array = array_intersect_key( $array, array_flip( $expectedKeys ) );

		if ( empty( $array ) ) {
			throw new InvalidArgumentException(
				'Invalid DonorInfo object, must have the exact following keys: ' . implode( ', ', $expectedKeys )
			);
		}

		// Cast array "address" to Give\ValueObjects\Address object.
		if ( ! empty( $array['address'] ) ) {
			$array['address'] = Address::fromArray( $array['address'] );
		}

		$cardInfo = new self();

		foreach ( $array as $key => $value ) {
			$cardInfo->{$key} = $value;
		}

		return $cardInfo;
	}
}
