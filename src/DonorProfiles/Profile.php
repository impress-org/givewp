<?php

namespace Give\DonorProfiles;

use \Give_Donor as DonorModel;

class Profile {

	protected $donor;

	public function __construct( int $donorId ) {
		$this->donor = new DonorModel( $donorId );
	}

	public function updateProfile( $profileData ) {
		//$this->donor->update([]);
		return [
			'updated' => true,
			'profile' => $this->getProfile(),
		];
	}

	/**
	 * Return array of donor profile data
	 *
	 * @return void
	 * @since 2.10.0
	 **/
	public function getProfile() {

		$titlePrefix = Give()->donor_meta->get_meta( $this->donor->id, '_give_donor_title_prefix', true );

		return [
			'name'              => give_get_donor_name_with_title_prefixes( $titlePrefix, $this->donor->name ),
			'emails'            => $this->donor->emails,
			'sinceLastDonation' => human_time_diff( strtotime( $this->donor->get_last_donation_date() ) ),
			'avatarUrl'         => give_validate_gravatar( $this->donor->email ) ? get_avatar_url( $this->donor->email, 140 ) : null,
			'sinceCreated'      => human_time_diff( strtotime( $this->donor->date_created ) ),
			'company'           => $this->donor->get_company_name(),
			'initials'          => $this->donor->get_donor_initals(),
			'titlePrefix'       => $titlePrefix,
			'addresses'         => $this->donor->address,
		];
	}
}
