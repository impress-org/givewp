<?php

namespace Give\DonorProfiles;

use Give\DonorProfiles\Factories\DonorFactory;

class Profile {

	protected $donor;
	protected $id;

	public function __construct( int $donorId ) {
		$donorFactory = new DonorFactory;
		$this->donor  = $donorFactory->make( $donorId );
	}

	/**
	 * Handles updating relevant profile fields in donor database and meta database
	 *
	 * @param object $data Object representing profile data to update
	 *
	 * @return array
	 *
	 * @since 2.11.0
	 */
	public function update( $data ) {

		// Handle updates to the donor meta table
		$this->updateDonorMetaDB( $data );

		// Handle updates to the donor table
		$this->updateDonorDB( $data );

		// Return updated donor profile data
		return $this->getProfileData();

	}

	/**
	 * Updates relevant profile fields found in meta database
	 *
	 * @param object $data Object representing profile data to update
	 *
	 * @return void
	 *
	 * @since 2.11.0
	 */
	protected function updateDonorMetaDB( $data ) {

		/**
		 * For simple meta updates, use this map to update the correct meta keys
		 * based on parameters in the REST request
		 **/

		$attributeMetaMap = [
			'firstName'   => '_give_donor_first_name',
			'lastName'    => '_give_donor_last_name',
			'titlePrefix' => '_give_donor_title_prefix',
			'avatarId'    => '_give_donor_avatar_id',
		];

		foreach ( $attributeMetaMap as $attribute => $metaKey ) {
			if ( property_exists( $data, $attribute ) ) {
				$this->donor->update_meta( $metaKey, $data->{$attribute} );
			}
		}

		/**
		 * For more complex meta updates, logic has been refactored into seperate methods
		 * - update additional emails
		 * - update addresses
		 */

		$this->updateDonorAdditionalEmailsMeta( isset( $data->additionalEmails ) ? $data->additionalEmails : [] );
		$this->updateDonorAddressMeta( isset( $data->primaryAddress ) ? $data->primaryAddress : null, isset( $data->additionalAddresses ) ? $data->additionalAddresses : [] );

	}

	/**
	 * Updates donor address fields found in meta database
	 *
	 * @param array|object $primaryAddress Array or object representing primary donor address
	 * @param array $additionalAddresses Array containing additional donor addresses
	 *
	 * @return void
	 *
	 * @since 2.11.0
	 */
	protected function updateDonorAddressMeta( $primaryAddress, $additionalAddresses ) {

		/**
		 * If a primary address is provided, update billing address with id '0'
		 */

		if ( ! empty( $primaryAddress ) ) {
			$this->donor->add_address( 'billing_0', (array) $primaryAddress );
		}

		/**
		 * Clear out existing additional addresses
		 */

		$storedAdditionalAddresses = $this->getStoredAdditionalAddresses();
		foreach ( $storedAdditionalAddresses as $key => $storedAdditionalAddress ) {
			$this->donor->remove_address( "billing_{$key}" );
		}

		/**
		 * If additional addresses are provided, add them to the donor meta table
		 */

		if ( ! empty( $additionalAddresses ) ) {
			foreach ( $additionalAddresses as $key => $additionalAddress ) {
				$addressId = 'billing_' . ( $key + 1 );
				$this->donor->add_address( $addressId, (array) $additionalAddress );
			}
		}
	}

	/**
	 * Retrieves additional addresses stored in meta database
	 *
	 * @return array
	 *
	 * @since 2.11.0
	 */
	protected function getStoredAdditionalAddresses() {
		$storedAddresses           = $this->donor->address;
		$storedAdditionalAddresses = [];

		if ( isset( $storedAddresses['billing'] ) ) {
			foreach ( $storedAddresses['billing'] as $key => $address ) {
				if ( $key !== 0 ) {
					$storedAdditionalAddresses[ $key ] = $address;
				}
			}
		}
		return $storedAdditionalAddresses;
	}

	/**
	 * Updates additional emails stored in meta database
	 *
	 * @param array $additionalEmails Array of additional emails to store
	 *
	 * @return void
	 *
	 * @since 2.11.0
	 */
	protected function updateDonorAdditionalEmailsMeta( $additionalEmails ) {

		/**
		 * Remove additional emails that exist in the donor meta table,
		 * but do not appear in the new array of additional emails
		 */

		$storedAdditionalEmails = $this->donor->get_meta( 'additional_email', false );
		$diffEmails             = array_diff( $storedAdditionalEmails, $additionalEmails );

		foreach ( $diffEmails as $diffEmail ) {
			$this->donor->delete_meta( 'additional_email', $diffEmail );
		}

		/**
		 * Add any new additional emails
		 */

		foreach ( $additionalEmails as $email ) {
			if ( ! in_array( $email, $storedAdditionalEmails ) ) {
				$this->donor->add_meta( 'additional_email', $email );
			}
		}
	}

	/**
	 * Updates relevant profile fields found in donor database
	 *
	 * @param object $data Object representing profile data to update
	 *
	 * @return void
	 * @since 2.11.0
	 */
	protected function updateDonorDB( $data ) {

		$updateArgs = [];

		if ( ! empty( $data->firstName ) && ! empty( $data->lastName ) ) {
			$updateArgs['name'] = "{$data->firstName} {$data->lastName}";
		}

		if ( ! empty( $data->primaryEmail ) ) {
			$updateArgs['email'] = $data->primaryEmail;
		}

		$this->donor->update( $updateArgs );

	}

	/**
	 * Return array of donor profile data
	 *
	 * @return void
	 * @since 2.11.0
	 **/
	public function getProfileData() {

		$titlePrefix = Give()->donor_meta->get_meta( $this->donor->id, '_give_donor_title_prefix', true );

		return [
			'name'              => give_get_donor_name_with_title_prefixes( $titlePrefix, $this->donor->name ),
			'firstName'         => $this->donor->get_first_name(),
			'lastName'          => $this->donor->get_last_name(),
			'emails'            => $this->donor->emails,
			'sinceLastDonation' => human_time_diff( strtotime( $this->donor->get_last_donation_date() ) ),
			'avatarUrl'         => $this->getAvatarUrl(),
			'avatarId'          => $this->getAvatarId(),
			'sinceCreated'      => human_time_diff( strtotime( $this->donor->date_created ) ),
			'company'           => $this->donor->get_company_name(),
			'initials'          => $this->donor->get_donor_initals(),
			'titlePrefix'       => $titlePrefix,
			'addresses'         => $this->donor->address,
			'isAnonymous'       => $this->donor->get_meta( '_give_anonymous_donor', false )[0] !== '0' ? 'private' : 'public',
		];
	}

	/**
	 * Returns profile model's donor id
	 *
	 * @return int
	 *
	 * @since 2.11.0
	 */
	public function getId() {
		return $this->donor->id;
	}

	/**
	 * Returns profile's avatar URL
	 *   *
	 * @return string
	 *
	 * @since 2.11.0
	 */
	public function getAvatarUrl() {
		$avatarId = $this->getAvatarId();
		if ( $avatarId ) {
			return wp_get_attachment_url( $avatarId );
		} else {
			return give_validate_gravatar( $this->donor->email ) ? get_avatar_url( $this->donor->email, 140 ) : null;
		}
	}

	/**
	 * Returns profile's avatar media ID
	 *   *
	 * @return int
	 *
	 * @since 2.11.0
	 */
	public function getAvatarId() {
		return $this->donor->get_meta( '_give_donor_avatar_id' );
	}

	/**
	 * Returns profile's stored country, or global default if none is set
	 *   *
	 * @return string
	 *
	 * @since 2.11.0
	 */
	public function getCountry() {
		$address = $this->donor->get_donor_address();
		if ( $address ) {
			return $address['country'];
		} else {
			return give_get_country();
		}
	}
}
