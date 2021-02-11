<?php

namespace Give\Tracking\TrackingData;

use Exception;
use Give\Helpers\ArrayDataSet;
use Give\Tracking\Contracts\TrackData;
use Give_Donors_Query;
use WP_Query;

/**
 * Class DonationMetricsData
 * @package Give\Tracking\TrackingData
 */
class DonationMetricsData implements TrackData {
	/**
	 * @inheritdoc
	 * @return array|void
	 */
	public function get() {
		return [
			'form_count'                   => $this->getDonationFormCount(),
			'donor_count'                  => $this->getDonorCount(),
			'avg_donation_amount_by_donor' => $this->getAvgDonationAmountByDonor(),
			'first_donation_date'          => $this->getFirstDonationDate(),
			'last_donation_date'           => $this->getLastDonationDate(),
			'revenue'                      => $this->getRevenueTillNow(),
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
				WHERE p.post_date<=%s
				AND post_status IN ({$statues})
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

	/**
	 * Returns donor count which donated greater then zero
	 *
	 * @since 2.10.0
	 * @return int
	 */
	private function getDonorCount() {
		$donorQuery = new Give_Donors_Query(
			[
				'number'          => -1,
				'count'           => true,
				'donation_amount' => [
					'compare' => '>',
					'amount'  => 0,
				],
			]
		);

		return (int) $donorQuery->get_donors();
	}

	/**
	 * Get average donation by donor.
	 *
	 * @since 2.10.0
	 * @return int
	 */
	private function getAvgDonationAmountByDonor() {
		try {
			$donationData = new DonationData();
			$amount       = (int) ( $donationData->getRevenueTillNow() / $this->getDonorCount() );
		} catch ( Exception $e ) {
			$amount = 0;
		}

		return $amount;
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
