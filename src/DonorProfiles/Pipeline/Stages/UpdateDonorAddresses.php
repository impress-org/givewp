<?php

namespace Give\DonorProfiles\Pipeline\Stages;

class UpdateDonorAddresses {

	protected $data;
	protected $donor;

	public function __invoke( $payload ) {

		$this->data  = $payload['data'];
		$this->donor = $payload['donor'];

		$this->updateAddressesInMetaDB();

		return $payload;

	}

	/**
	 * Updates donor address fields found in meta database
	 *
	 * @return void
	 *
	 * @since 2.11.0
	 */
	protected function updateAddressesInMetaDB() {

		$primaryAddress      = isset( $this->data['primaryAddress'] ) ? $this->data['primaryAddress'] : null;
		$additionalAddresses = isset( $this->data['additionalAddresses'] ) ? $this->data['additionalAddresses'] : [];

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
}
