<?php

namespace Give\DonorDashboards\Pipeline\Stages;

use Give\DonorDashboards\Pipeline\Stages\Stage;

/**
 * @since 2.10.0
 */
class UpdateDonorAddresses implements Stage {

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
	 * @since 2.10.0
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
		 * If additional addresses are provided, add them to the donor meta table
		 */

		if ( ! empty( $additionalAddresses ) ) {
			foreach ( $additionalAddresses as $key => $additionalAddress ) {
				$addressId = 'billing_' . ( $key + 1 );
				$this->donor->add_address( $addressId, (array) $additionalAddress );
			}
		}

		/**
		 * Clear deleted address keys
		 */

		$totalStoredAddresses = isset( $this->donor->address['billing'] ) ? count( $this->donor->address['billing'] ) : 0;
		$totalNewAddresses    = count( $additionalAddresses ) + 1;

		if ( $totalStoredAddresses > $totalNewAddresses ) {
			$key = $totalNewAddresses;
			while ( $key < $totalStoredAddresses ) {
				$this->donor->remove_address( "billing_{$key}" );
				$key++;
			}
		}
	}
}
