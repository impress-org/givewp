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

		return $this->getProfileData();

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

		$this->updateDonorAdditionalEmailsMeta( isset( $data->addiitonalEmails ) ? $data->addiitonalEmails : [] );
		$this->updateDonorAddressMeta( isset( $data->primaryAddress ) ? $data->primaryAddress : null, isset( $data->additionalAddresses ) ? $data->additionalAddresses : [] );

	}

	protected function updateDonorAddressMeta( $primaryAddress, $additionalAddresses ) {

		if ( ! empty( $primaryAddress ) ) {
			$this->donor->add_address( 'billing_0', (array) $primaryAddress );
		}

		$storedAddresses           = $this->donor->address;
		$storedAdditionalAddresses = [];
		foreach ( $storedAddresses['billing'] as $key => $address ) {
			if ( $key !== 0 ) {
				$storedAdditionalAddresses[ $key ] = $address;
			}
		}

		foreach ( $storedAdditionalAddresses as $key => $storedAdditionalAddress ) {
			$this->donor->remove_address( "billing_{$key}" );
		}

		if ( ! empty( $additionalAddresses ) ) {
			foreach ( $additionalAddresses as $key => $additionalAddress ) {
				$addressId = 'billing_' . ( $key + 1 );
				$this->donor->add_address( $addressId, (array) $additionalAddress );
			}
		}
	}

	protected function updateDonorAdditionalEmailsMeta( $additionalEmails ) {
		$storedAdditionalEmails = $this->donor->get_meta( 'additional_email', false );
		$diffEmails             = array_diff( $storedAdditionalEmails, $additionalEmails );

		foreach ( $diffEmails as $diffEmail ) {
			$this->donor->delete_meta( 'additional_email', $diffEmail );
		}

		foreach ( $additionalEmails as $email ) {
			if ( ! in_array( $email, $storedAdditionalEmails ) ) {
				$this->donor->add_meta( 'additional_email', $email );
			}
		}
	}

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
