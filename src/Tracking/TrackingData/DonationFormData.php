<?php
namespace Give\Tracking\TrackingData;

use Give\Tracking\Contracts\TrackData;
use WP_Query;

/**
 * Class DonationFormData
 *
 * Represents donation form data
 *
 * @package Give\Tracking\TrackingData
 *
 * @since 2.10.0
 */
class DonationFormData implements TrackData {

	/**
	 * @inheritdoc
	 * @return array|void
	 */
	public function get() {
		return [
			'donationFormCount' => $this->getDonationFormCount(),
		];
	}

	/**
	 * Returns donation form count
	 *
	 * @since 2.10.0
	 * @return int
	 */
	private function getDonationFormCount() {
		$formQuery = new WP_Query(
			[
				'post_type' => 'give_forms',
				'status'    => 'publish',
				'fields'    => 'ids',
				'number'    => -1,
			]
		);

		return $formQuery->found_posts;
	}
}
