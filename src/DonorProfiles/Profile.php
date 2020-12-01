<?php

namespace Give\DonorProfiles;

use \Give_Donor as DonorModel;

class Profile {

	protected $donor;
	protected $id;

	public function __construct( int $donorId ) {
		$this->id    = $donorId;
		$this->donor = new DonorModel( $donorId );
	}

	public function update( $data ) {

		$this->updateDonorMetaDB( $data );
		$this->updateDonorDB( $data );

		$this->updateEmails( $data->primaryEmail, $data->additionalEmails );

		return $this->getProfileData();

	}

	public function updateEmails( $primaryEmail, $additionalEmails ) {
		$storedAdditionalEmails = $this->donor->get_meta( 'additional_email' );
		foreach ( $storedAdditionalEmails as $key => $storedAdditionalEmail ) {
			$this->donor->remove_email( $storedAdditionalEmail );
		}

		$this->donor->add_email( $primaryEmail, true );

		foreach ( $additionalEmails as $key => $additionalEmail ) {
			error_log( serialize( $additionalEmail ) );
			$this->donor->add_email( $additionalEmail );
		}
	}

	protected function updateDonorMetaDB( $data ) {

		$attributeMetaMap = [
			'firstName'   => '_give_donor_first_name',
			'lastName'    => '_give_donor_last_name',
			'titlePrefix' => '_give_donor_title_prefix',
		];

		foreach ( $attributeMetaMap as $attribute => $metaKey ) {
			if ( property_exists( $data, $attribute ) ) {
				$this->donor->update_meta( $metaKey, $data->{$attribute} );
			}
		}

	}

	protected function updateDonorDB( $data ) {

		$updateArgs = [];

		if ( ! empty( $data->firstName ) && ! empty( $data->lastName ) ) {
			$updateArgs['name'] = "{$data->firstName} {$data->lastName}";
		}

		$this->donor->update( $updateArgs );

	}

	/**
	 * Return array of donor profile data
	 *
	 * @return void
	 * @since 2.10.0
	 **/
	public function getProfileData() {

		$titlePrefix = Give()->donor_meta->get_meta( $this->donor->id, '_give_donor_title_prefix', true );

		return [
			'name'              => give_get_donor_name_with_title_prefixes( $titlePrefix, $this->donor->name ),
			'firstName'         => $this->donor->get_first_name(),
			'lastName'          => $this->donor->get_last_name(),
			'emails'            => $this->donor->emails,
			'sinceLastDonation' => human_time_diff( strtotime( $this->donor->get_last_donation_date() ) ),
			'avatarUrl'         => give_validate_gravatar( $this->donor->email ) ? get_avatar_url( $this->donor->email, 140 ) : null,
			'sinceCreated'      => human_time_diff( strtotime( $this->donor->date_created ) ),
			'company'           => $this->donor->get_company_name(),
			'initials'          => $this->donor->get_donor_initals(),
			'titlePrefix'       => $titlePrefix,
			'addresses'         => $this->donor->address,
			'isAnonymous'       => $this->donor->get_meta( '_give_anonymous_donor', false )[0] !== '0' ? 'private' : 'public',
		];
	}

	public function getId() {
		return $this->id;
	}
}
