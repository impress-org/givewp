<?php
namespace Give\Tracking\TrackingData;

use Give\Helpers\ArrayDataSet;
use Give\Tracking\Contracts\TrackData;
use Give\ValueObjects\Money;

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
			'firstDonationDate' => $this->getFirstDonationDate(),
			'lastDonationDate'  => $this->getLastDonationDate(),
			'revenue'           => $this->getRevenueTillNow(),
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
				FROM {$wpdb->posts}
				WHERE post_status IN ({$this->getDonationStatuses()})
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
				FROM {$wpdb->posts}
				WHERE post_status IN ({$this->getDonationStatuses()})
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
	 * @return string
	 */
	public function getRevenueTillNow() {
		global $wpdb;

		$currency = give_get_option( 'currency' );
		$statues  = $this->getDonationStatuses();

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT SUM(amount)
				FROM {$wpdb->give_revenue} as r
				INNER JOIN {$wpdb->posts} as p
				ON r.donation_id=p.id
				WHERE p.post_date<=%s
				AND post_status IN ({$statues})
				",
				current_time( 'mysql' )
			)
		);
		return $result ?: '';
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
