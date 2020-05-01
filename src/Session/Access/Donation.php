<?php

namespace Give\Session\Access;

use DateTime;
use Give\Session\Objects\Donation as DonationObject;

/**
 * Class Donation
 *
 * @package Give\Session\Access
 */
class Donation extends Access {
	/**
	 * Session Id
	 *
	 * @since 2.7.0
	 * @var string
	 */
	protected $sessionKey = 'give_purchase';

	/**
	 * Donation object.
	 *
	 * Since 2.7.0
	 *
	 * @var DonationObject
	 */
	protected $dataObj;

	/**
	 * property vs session key array.
	 * It is useful to map array keys to class properties.
	 *
	 * @var array
	 */
	private $renameTo = [
		'user_email'  => 'donorEmail',
		'user_info'   => 'donorInfo',
		'post_data'   => 'formEntries',
		'donation_id' => 'id',
		'price'       => 'totalAmount',
		'gateway'     => 'paymentGateway',
		'date'        => 'createdAt',
	];

	/**
	 * Map array keys to class properties
	 *
	 * @param array $data
	 *
	 * @return DonationObject
	 * @since 2.7.0
	 */
	protected function convertToObject( $data ) {
		// Cast date string to DateTime object.
		$data['date'] = DateTime::createFromFormat( 'Y-m-d H:i:s', $data['date'] );

		// Rename key if property name exist for them.
		foreach ( $data as $key => $value ) {
			if ( array_key_exists( $key, $this->renameTo ) ) {
				$data[ $this->renameTo[ $key ] ] = $value;
				unset( $data[ $key ] );
			}
		}

		// Rename unknown keys.
		$data = $this->renameArrayKeysToPropertyNames( $data );

		return DonationObject::fromArray( $data );
	}

	/**
	 * Get donation id.
	 *
	 * @return int
	 *
	 * @since 2.7.0
	 */
	public function getDonationId() {
		return absint( $this->getByKey( 'id' ) );
	}
}
