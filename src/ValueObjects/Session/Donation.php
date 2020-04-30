<?php
namespace Give\ValueObjects\Session;

use DateTime;
use Give\Helpers\ArrayDataSet;
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

		$array = array_intersect_key( $array, array_flip( $expectedKeys ) );

		if ( ! ArrayDataSet::hasRequiredKeys( $array, $expectedKeys ) ) {
			throw new InvalidArgumentException(
				'Invalid Donation object, must have the exact following keys: ' . implode( ', ', $expectedKeys )
			);
		}

		$donation = new self();

		foreach ( $array as $key => $value ) {
			if ( array_key_exists( $key, $donation->caseTo ) ) {
				$donation->{$key} = $donation->caseTo[ $key ]::fromArray( $value );
				continue;
			}

			$donation->{$key} = $value;
		}

		/**
		 * Filter the donation object
		 *
		 * @param Donation $donation
		 */
		return apply_filters( 'give_session_donation_object', $donation, $array );
	}
}
