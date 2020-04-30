<?php
namespace Give\ValueObjects\Session;

use DateTime;
use Give\Helpers\ArrayDataSet;
use Give\ValueObjects\CardInfo;
use Give\ValueObjects\DonorInfo;
use Give\ValueObjects\ValueObjects;
use InvalidArgumentException;
use stdClass;

class Donation implements ValueObjects {
	/**
	 * Donation id.
	 *
	 * @since 2.7.0
	 * @var array
	 */
	public $id;

	/**
	 * Sanitized donation total amount.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $totalAmount;

	/**
	 * Donation purchase key.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $purchaseKey;

	/**
	 * Donor email.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $donorEmail;

	/**
	 * Donor email.
	 *
	 * @since 2.7.0
	 * @var DateTime
	 */
	public $createdAt;

	/**
	 * Payment gateway id.
	 *
	 * @since 2.7.0
	 * @var array
	 */
	public $paymentGateway;

	/**
	 * Array of properties  and there cast type.
	 *
	 * @var ValueObjects[]
	 */
	private $caseTo = [
		'formEntries' => FormEntries::class,
		'donorInfo'   => DonorInfo::class,
		'cardInfo'    => CardInfo::class,
	];

	/**
	 * Take array and return object
	 *
	 * @param $array
	 *
	 * @return Donation
	 */
	public static function fromArray( $array ) {
		$expectedKeys = [ 'id', 'totalAmount', 'purchaseKey', 'donorEmail', 'createdAt', 'paymentGateway', 'formEntries', 'cardInfo', 'donorInfo' ];

		if ( ! ArrayDataSet::hasRequiredKeys( $array, $expectedKeys ) ) {
			throw new InvalidArgumentException(
				'Invalid Donation object, must have the exact following keys: ' . implode( ', ', $expectedKeys )
			);
		}

		$donation = new self();

		$array['donorInfo'] = $donation->renameKeyInDonorInfo( $array['donorInfo'] );
		$array['cardInfo']  = $donation->filterCardInfoKeys( $array['cardInfo'] );

		foreach ( $array as $key => $value ) {
			if ( array_key_exists( $key, $donation->caseTo ) ) {
				$donation->{$key} = $donation->caseTo[ $key ]::fromArray( $value );
				continue;
			}

			$donation->{$key} = is_array( $value ) ?
				json_decode( json_encode( $value ) ) // Convert unlisted array type session data to stdClass object
				: $value;
		}

		return $donation;
	}

	/**
	 * Rename array key in donor info
	 *
	 * @since 2.7.0
	 * @param array $array
	 *
	 * @return array
	 */
	private function renameKeyInDonorInfo( $array ) {
		return ArrayDataSet::renameKeys(
			$array,
			[
				'id'    => 'wpUserId',
				'title' => 'honorific',
			]
		);
	}

	/**
	 * Filter array keys in card info
	 *
	 * @since 2.7.0
	 * @param $array
	 * @return array
	 */
	private function filterCardInfoKeys( $array ) {
		$array = ArrayDataSet::removePrefixFromArrayKeys( $array, [ 'card' ] );
		$array = ArrayDataSet::renameKeys(
			$array,
			[
				'address'  => 'line1',
				'address2' => 'line2',
			]
		);
		$array = ArrayDataSet::moveArrayItemsUnderArrayKey( $array, [ 'line1', 'line2', 'city', 'state', 'country', 'zip' ], 'address' );

		// Rename zip to postal code.
		$array['address']['postalCode'] = $array['address']['zip'];
		unset( $array['address']['zip'] );

		return $array;
	}
}
