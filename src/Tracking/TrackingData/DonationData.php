<?php
namespace Give\Tracking\TrackingData;

use Give\Framework\Collection;
use Give\ValueObjects\Money;

/**
 * Class DonationData
 * @package Give\Tracking\TrackingData
 *
 * Represents donation data.
 *
 * @since 2.10.0
 */
class DonationData implements Collection {
	/**
	 * @inheritdoc
	 * @return array|void
	 */
	public function get() {
		return [
			'donation' => [
				'firstDonationDate' => $this->getFirstDonationDate(),
				'lastDonationDate'  => $this->getLastDonationDate(),
				'revenue'           => $this->getRevenueTillNow(),
			],
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

		return $date ? strtotime( $date ) : 'NULL';
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

		return $date ? strtotime( $date ) : 'NULL';
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
		$result   = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT SUM(amount)
				FROM {$wpdb->give_revenue} as r
				INNER JOIN {$wpdb->posts} as p
				ON r.donation_id=p.id
				WHERE p.post_date<=%s
				AND post_status IN ({$this->getDonationStatuses()})
				",
				current_time( 'mysql' )
			)
		);
		return $result ? Money::ofMinor( $result, $currency )->getAmount() : '';
	}

	/**
	 * Get donation statuses.
	 *
	 * @since 2.10.0
	 *
	 * @return string
	 */
	private function getDonationStatuses() {
		$statuses = implode(
			'\',\'',
			[
				'publish', // One time donation
				'give_subscription', // Renewal
			]
		);

		return "'{$statuses}'";
	}
}
