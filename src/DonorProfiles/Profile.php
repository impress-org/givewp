<?php

namespace Give\DonorProfiles;

use Give\DonorProfiles\Factories\DonorFactory;
use Give\DonorProfiles\Pipeline\DonorProfilePipeline;
use Give\DonorProfiles\Pipeline\Stages\UpdateDonorName;
use Give\DonorProfiles\Pipeline\Stages\UpdateDonorAvatar;
use Give\DonorProfiles\Pipeline\Stages\UpdateDonorEmails;
use Give\DonorProfiles\Pipeline\Stages\UpdateDonorAddresses;


class Profile {

	protected $donor;
	protected $id;

	public function __construct( $donorId ) {
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
	 * @since 2.10.0
	 */
	public function update( $data ) {

		$pipeline = ( new DonorProfilePipeline )
			->pipe( new UpdateDonorName )
			->pipe( new UpdateDonorAvatar )
			->pipe( new UpdateDonorEmails )
			->pipe( new UpdateDonorAddresses );

		$pipeline->process(
			[
				'data'  => $data,
				'donor' => $this->donor,
			]
		);

		// Return updated donor profile data
		return $this->getProfileData();

	}

	/**
	 * Return array of donor profile data
	 *
	 * @since 2.10.0
	 *
	 * @return array
	 */
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
			'titlePrefix'       => $this->getTitlePrefix(),
			'addresses'         => $this->donor->address,
			'isAnonymous'       => $this->donor->get_meta( '_give_anonymous_donor', true ) !== '0' ? 'private' : 'public',
		];
	}

	/**
	 * Returns profile model's donor id
	 *
	 * @return int
	 *
	 * @since 2.10.0
	 */
	public function getId() {
		return $this->donor->id;
	}

	/**
	 * Returns donor's title prefix
	 *   *
	 * @return string
	 *
	 * @since 2.10.0
	 */
	public function getTitlePrefix() {
		return Give()->donor_meta->get_meta( $this->donor->id, '_give_donor_title_prefix', true );
	}

	/**
	 * Returns profile's avatar URL
	 *   *
	 * @return string
	 *
	 * @since 2.10.0
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
	 * @since 2.10.0
	 */
	public function getAvatarId() {
		return $this->donor->get_meta( '_give_donor_avatar_id' );
	}

	/**
	 * Returns profile's stored country, or global default if none is set
	 *   *
	 * @return string
	 *
	 * @since 2.10.0
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
