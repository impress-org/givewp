<?php

namespace Give\DonorDashboards\Pipeline\Stages;

use Give\DonorDashboards\Pipeline\Stages\Stage;

/**
 * @since 2.10.0
 */
class UpdateDonorName implements Stage {

	protected $data;
	protected $donor;

	public function __invoke( $payload ) {

		$this->data  = $payload['data'];
		$this->donor = $payload['donor'];

		$this->updateNameInMetaDB();
		$this->updateNameInDonorDB();

		return $payload;

	}

	protected function updateNameInMetaDB() {
		$attributeMetaMap = [
			'firstName'   => '_give_donor_first_name',
			'lastName'    => '_give_donor_last_name',
			'titlePrefix' => '_give_donor_title_prefix',
		];

		foreach ( $attributeMetaMap as $attribute => $metaKey ) {
			if ( key_exists( $attribute, $this->data ) ) {
				$this->donor->update_meta( $metaKey, $this->data[ $attribute ] );
			}
		}
	}

	protected function updateNameInDonorDB() {

		$updateArgs = [];
		if ( ! empty( $this->data['firstName'] ) && ! empty( $this->data['lastName'] ) ) {
			$firstName          = $this->data['firstName'];
			$lastName           = $this->data['lastName'];
			$updateArgs['name'] = "{$firstName} {$lastName}";
		}

		$this->donor->update( $updateArgs );
	}
}
