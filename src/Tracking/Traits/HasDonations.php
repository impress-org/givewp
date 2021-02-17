<?php

namespace Give\Tracking\Traits;

use Give\Helpers\ArrayDataSet;
use Give\Tracking\TrackJob;

/**
 * Trait HasDonations
 * @package Give\Tracking\Traits
 *
 * @since 2.10.0
 */
trait HasDonations {
	/**
	 * Return donation ids after last tracked request date.
	 *
	 * @sicne 2.10.0
	 * @return array
	 */
	private function getNewDonationIdsSinceLastRequest() {
		global $wpdb;

		$statues     = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote(
			[
				'publish', // One time donation
				'give_subscription', // Renewal
			]
		);
		$defaultTime = strtotime( 'today', current_time( 'timestamp' ) );
		$time        = date( 'Y-m-d H:i:s', get_option( TrackJob::LAST_REQUEST_OPTION_NAME, $defaultTime ) );

		return $wpdb->get_col(
			"
			SELECT ID
			FROM {$wpdb->posts} as p
			INNER JOIN {$wpdb->donationmeta} as dm
			ON p.id=dm.donation_id
			WHERE post_date_gmt >= '{$time}'
			AND post_status IN ({$statues})
			AND post_type='give_payment'
			AND dm.meta_key='_give_payment_mode'
			AND dm.meta_value='live'
			"
		);
	}
}
