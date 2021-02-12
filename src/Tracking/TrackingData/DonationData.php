<?php
namespace Give\Tracking\TrackingData;

use Give\Helpers\ArrayDataSet;
use Give\Tracking\Contracts\TrackData;

/**
 * Class DonationData
 *
 * Represents donation data.
 *
 * @package Give\Tracking\TrackingData
 *
 * @since 2.10.0
 */
class DonationData implements TrackData {
	/**
	 * @inheritdoc
	 * @return array|void
	 */
	public function get() {
		return [
			'first_donation_date' => $this->getFirstDonationDate(),
			'last_donation_date'  => $this->getLastDonationDate(),
			'revenue'             => $this->getRevenueTillNow(),
		];
	}

	/**
	 * Get first donation date.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	private function getFirstDonationDate() {
		global $wpdb;

		$date = $wpdb->get_var(
			"
			SELECT post_date_gmt
			FROM {$wpdb->posts} as p
			INNER JOIN {$wpdb->donationmeta} as dm
			ON p.id=dm.donation_id
			WHERE post_status IN ({$this->getDonationStatuses()})
			AND dm.meta_key='_give_payment_mode'
			AND dm.meta_value='live'
			ORDER BY post_date_gmt DESC
			LIMIT 1
			"
		);

		return $date ? strtotime( $date ) : '';
	}

	/**
	 * Get last donation date.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	private function getLastDonationDate() {
		global $wpdb;

		$date = $wpdb->get_var(
			"
			SELECT post_date_gmt
			FROM {$wpdb->posts} as p
			INNER JOIN {$wpdb->donationmeta} as dm
			ON p.id=dm.donation_id
			WHERE post_status IN ({$this->getDonationStatuses()})
			AND dm.meta_key='_give_payment_mode'
			AND dm.meta_value='live'
			ORDER BY post_date_gmt ASC
			LIMIT 1
			"
		);

		return $date ? strtotime( $date ) : '';
	}

	/**
	 * Returns revenue till current date.
	 *
	 * @since 2.10.0
	 * @return int
	 */
	public function getRevenueTillNow() {
		global $wpdb;

		$statues = $this->getDonationStatuses();

		$result = (int) $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT SUM(amount)
				FROM {$wpdb->give_revenue} as r
				INNER JOIN {$wpdb->posts} as p
				ON r.donation_id=p.id
				INNER JOIN {$wpdb->donationmeta} as dm
				ON p.id=dm.donation_id
				WHERE p.post_date<=%s
				AND post_status IN ({$statues})
				AND dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
				",
				current_time( 'mysql' )
			)
		);
		return $result ?: 0;
	}

	/**
	 * Get donation statuses string.
	 *
	 * @since 2.10.0
	 *
	 * @return string
	 */
	private function getDonationStatuses() {
		return ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote(
			[
				'publish', // One time donation
				'give_subscription', // Renewal
			]
		);
	}
}
