<?php
namespace Give\Session\SessionDonation;

use DateTime;
use Give\Session\SessionDonation\SessionObjects\Donation as DonationObject;
use Give\Session\Accessor;
use Give\Session\SessionDonation\SessionObjects\FormEntry;

/**
 * Class Donation
 *
 * This class provide way to access donation session data.
 *
 * @package Give\Session\Access
 */
class DonationAccessor extends Accessor {
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
		'post_data'   => 'formEntry',
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
	 * @return DonationObject|null
	 * @since 2.7.0
	 */
	protected function convertToObject( $data ) {
		if ( ! $data ) {
			return null;
		}

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
		if ( $donationId = $this->getByKey( 'id' ) ) {
			return absint( $donationId );
		}

		return null;
	}

	/**
	 * Get donation id.
	 *
	 * @return int
	 *
	 * @since 2.7.0
	 */
	public function getFormId() {
		/* @var FormEntry $data */
		if ( $data = $this->getByKey( 'formEntry' ) ) {
			return absint( $data->formId );
		}

		return null;
	}
}
