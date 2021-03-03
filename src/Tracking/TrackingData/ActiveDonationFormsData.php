<?php

namespace Give\Tracking\TrackingData;

use Give\Helpers\ArrayDataSet;

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
	protected function setDonationIds() {
		global $wpdb;

		$statues = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote(
			[
				'publish', // One time donation
				'give_subscription', // Renewal
			]
		);

		$this->donationIds = $wpdb->get_col(
			"
			SELECT ID
			FROM {$wpdb->posts} as p
				INNER JOIN {$wpdb->donationmeta} as dm ON p.id=dm.donation_id
			WHERE post_status IN ({$statues})
				AND post_type='give_payment'
				AND dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
			"
		);

		return $this;
	}
}
