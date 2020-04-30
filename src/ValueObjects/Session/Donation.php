<?php
namespace Give\ValueObjects\Session;

use DateTime;
use Give\ValueObjects\CardInfo;
use Give\ValueObjects\DonorInfo;
use Give\ValueObjects\ValueObjects;
use InvalidArgumentException;

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
	public $creationDate;

	/**
	 * Payment gateway id.
	 *
	 * @since 2.7.0
	 * @var array
	 */
	public $paymentGateway;


	/**
	 * Take array and return object
	 *
	 * @param $array
	 *
	 * @return Donation
	 */
	public static function fromArray( $array ) {
		$expectedKeys = [ 'id', 'price', 'totalAmount', 'purchaseKey', 'donorEmail', 'creationDate', 'paymentGateway' ];

		$hasRequiredKeys = (bool) array_intersect_key( $array, array_flip( $expectedKeys ) );

		if ( ! $hasRequiredKeys ) {
			throw new InvalidArgumentException(
				'Invalid Donation object, must have the exact following keys: ' . implode( ', ', $expectedKeys )
			);
		}

		$donation = new self();
		foreach ( $array as $key => $value ) {
			$donation->{$key} = $value;
		}

		if ( property_exists( $donation, 'formEntries' ) ) {
			$donation->formEntries = FormEntries::fromArray( $donation->formEntries );
		}

		if ( property_exists( $donation, 'donorInfo' ) ) {
			$donation->donorInfo = DonorInfo::fromArray( $donation->donorInfo );
		}

		if ( property_exists( $donation, 'cardInfo' ) ) {
			$donation->cardInfo = CardInfo::fromArray( $donation->cardInfo );
		}

		/**
		 * Filter the donation object
		 *
		 * @param Donation $donation
		 */
		return apply_filters( 'give_session_donation_object', $donation, $array );
	}
}
