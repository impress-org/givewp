<?php

namespace Give\Tracking\TrackingData;

use Give\Helpers\ArrayDataSet;
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

		$statues = DonationStatuses::getCompletedDonationsStatues( true );

		$this->formIds = $wpdb->get_col(
			"
			SELECT DISTINCT meta_value
			FROM {$wpdb->donationmeta} as dm
				INNER JOIN {$wpdb->posts} as p ON dm.donation_id = p.ID
				INNER JOIN {$wpdb->donationmeta} as dm2 ON dm.donation_id = dm2.donation_id
			WHERE p.post_status IN ({$statues})
			  	AND p.post_status='give_payment'
				AND dm2.meta_key='_give_payment_mode'
				AND dm2.meta_value='live'
				AND dm.meta_key='_give_payment_form_id'
			"
		);

		return $this;
	}
}
