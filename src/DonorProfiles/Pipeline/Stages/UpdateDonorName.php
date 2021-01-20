<?php

namespace Give\DonorProfiles\Pipeline\Stages;

class UpdateDonorName {

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
			if ( property_exists( $this->data, $attribute ) ) {
				$this->donor->update_meta( $metaKey, $this->data->{$attribute} );
			}
		}
	}

	protected function updateNameInDonorDB() {

		$updateArgs = [];
		if ( ! empty( $this->data->firstName ) && ! empty( $this->data->lastName ) ) {
			$updateArgs['name'] = "{$this->data->firstName} {$this->data->lastName}";
		}

		$this->donor->update( $updateArgs );
	}
}
