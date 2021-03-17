<?php

namespace Give\DonorDashboards\Pipeline\Stages;

use Give\DonorDashboards\Pipeline\Stages\Stage;

/**
 * @since 2.10.0
 */
class UpdateDonorAnonymousGiving implements Stage {

	protected $data;
	protected $donor;

	public function __invoke( $payload ) {

		$this->data  = $payload['data'];
		$this->donor = $payload['donor'];

		$this->updateDonorAnonymousGivingInMetaDB();

		return $payload;

	}

	protected function updateDonorAnonymousGivingInMetaDB() {
		$attributeMetaMap = [
			'isAnonymous' => '_give_anonymous_donor',
		];

		foreach ( $attributeMetaMap as $attribute => $metaKey ) {
			if ( key_exists( $attribute, $this->data ) ) {
				$this->donor->update_meta( $metaKey, $this->data[ $attribute ] );
			}
		}
	}
}
