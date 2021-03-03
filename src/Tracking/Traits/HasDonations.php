<?php

namespace Give\Tracking\Traits;

use Give\Helpers\ArrayDataSet;
use Give\Tracking\Repositories\TrackEvents;

/**
 * Trait HasDonations
 * @package Give\Tracking\Traits
 *
 * @since 2.10.0
 */
trait HasDonations {
	/**
	 * @var TrackEvents
	 */
	protected $trackEvents;

	/**
	 * Return donation ids after last tracked request date.
	 *
	 * @sicne 2.10.0
	 * @return array
	 */
	private function getNewDonationIdsSinceLastRequest() {
		global $wpdb;

		$statues = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote(
			[
				'publish', // One time donation
				'give_subscription', // Renewal
			]
		);
		$time    = $this->trackEvents->getRequestTime();

		return $wpdb->get_col(
			"
			SELECT ID
			FROM {$wpdb->posts} as p
				INNER JOIN {$wpdb->donationmeta} as dm ON p.id=dm.donation_id
			WHERE post_date >= '{$time}'
				AND post_status IN ({$statues})
				AND post_type='give_payment'
				AND dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
			"
		);
	}
}
