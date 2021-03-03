<?php

namespace Give\Tracking\TrackingData;

use Give\Tracking\Helpers\DonationStatuses;

/**
 * Class AllActiveDonationFormsData
 * @package Give\Tracking\TrackingData
 *
 * @since 2.10.0
 */
class ActiveDonationFormsData extends DonationFormsData {
	/**
	 * Set donation ids.
	 *
	 * @since 2.10.0
	 *
	 * @return DonationFormsData
	 */
	protected function setFormIdsByDonationIds() {
		global $wpdb;

		$this->formIds = $wpdb->get_col(
			"
			SELECT DISTINCT r.form_id
			FROM {$wpdb->give_revenue} as r
				INNER JOIN {$wpdb->donationmeta} as dm ON r.donation_id = dm.donation_id
			WHERE dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
			"
		);

		return $this;
	}
}
