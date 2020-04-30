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
		$expectedKeys = [ 'cardName', 'firstName', 'email' ];

		$hasRequiredKeys = (bool) array_intersect_key( $array, array_flip( $expectedKeys ) );

		if ( ! $hasRequiredKeys ) {
			throw new InvalidArgumentException(
				'Invalid DonorInfo object, must have the exact following keys: ' . implode( ', ', $expectedKeys )
			);
		}

		$cardInfo = new self();

		// Rename and group array data.
		$renameTo = [
			'address'  => 'line1',
			'address2' => 'line2',
		];
		$array    = $cardInfo->removePrefixFromArrayKey( $array );
		$array    = ArrayDataSet::renameKeys( $array, $renameTo );
		$array    = $cardInfo->moveAddressItemsToGroup( $array );

		// Cast array "address" to Give\ValueObjects\Address object.
		$array['address'] = Address::fromArray( $array['address'] );

		foreach ( $array as $key => $value ) {
			$cardInfo->{$key} = $value;
		}

		/**
		 * Filter the donor info object
		 *
		 * @param CardInfo $cardInfo
		 */
		return apply_filters( 'give_card_info_object', $cardInfo, $array );
	}

	/**
	 * Remove prefix from array key.
	 *
	 * This function will remove card prefix from array key.
	 *
	 * @param $array
	 *
	 * @return array
	 */
	private function removePrefixFromArrayKey( $array ) {
		foreach ( $array as $key => $value ) {
			$newKey = lcfirst( str_replace( 'card', '', $key ) );

			if ( $key !== $newKey ) {
				unset( $array[ $key ] );
			}

			if ( is_array( $value ) ) {
				$array[ $newKey ] = $this->removePrefixFromArrayKey( $value );
				continue;
			}

			$array[ $newKey ] = $value;

		}

		return $array;
	}

	/**
	 * Return array with grouped address items.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	private function moveAddressItemsToGroup( $array ) {
		$addressItems = [ 'line1', 'line2', 'city', 'state', 'country', 'zip' ];

		foreach ( $addressItems as $key ) {
			if ( array_key_exists( $key, $array ) ) {
				$array['address'][ $key ] = $array[ $key ];
				unset( $array[ $key ] );
			}
		}

		// Rename zip to postal code.
		$array['address']['postalCode'] = $array['zip'];
		unset( $array['address']['zip'] );

		return $array;
	}
}
