<?php

namespace Give\DonorProfiles\Pipeline\Stages;

class UpdateDonorAvatar {

	protected $data;
	protected $donor;

	public function __invoke( $payload ) {

		$this->data  = $payload['data'];
		$this->donor = $payload['donor'];

		$this->updateAvatarInMetaDB();

		return $payload;

	}

	protected function updateAvatarInMetaDB() {
		$attributeMetaMap = [
			'avatarId' => '_give_donor_avatar_id',
		];

		foreach ( $attributeMetaMap as $attribute => $metaKey ) {
			if ( key_exists( $attribute, $this->data ) ) {
				$this->donor->update_meta( $metaKey, $this->data[ $attribute ] );
			}
		}
	}
}
