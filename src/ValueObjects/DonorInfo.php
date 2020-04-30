<?php

namespace Give\ValueObjects;

use InvalidArgumentException;

class DonorInfo implements ValueObjects {
	/**
	 * Donor id.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Primary email.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * Donor address.
	 *
	 * @var Address
	 */
	public $address;

	/**
	 * First name.
	 *
	 * @var string
	 */
	public $firstName;

	/**
	 * Last name.
	 *
	 * @var string
	 */
	public $lastName;

	/**
	 * Take array and return object.
	 *
	 * @param $array
	 *
	 * @return DonorInfo
	 */
	public static function fromArray( $array ) {
		$expectedKeys = [ 'id', 'firstName', 'email' ];

		$hasRequiredKeys = (bool) array_intersect_key( $array, array_flip( $expectedKeys ) );

		if ( ! $hasRequiredKeys ) {
			throw new InvalidArgumentException(
				'Invalid DonorInfo object, must have the exact following keys: ' . implode( ', ', $expectedKeys )
			);
		}

		$donation = new self();
		foreach ( $array as $key => $value ) {
			$donation->{$key} = $value;
		}

		/**
		 * Filter the donor info object
		 *
		 * @param DonorInfo $donation
		 */
		return apply_filters( 'give_session_donor_info_object', $donation, $array );
	}
}
