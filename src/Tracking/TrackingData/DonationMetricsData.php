<?php

namespace Give\Tracking\TrackingData;

use Give\Tracking\Contracts\TrackData;
use Give_Donors_Query;
use WP_Query;

/**
 * Class DonationMetricsData
 * @package Give\Tracking\TrackingData
 *
 * @unreleased
 */
class DonationMetricsData implements TrackData {
	private $donationData = [];

	/**
	 * @inheritdoc
	 * @return array|void
	 */
	public function get() {
		$this->donationData = ( new DonationData() )->get();

		$data = [
			'form_count'                   => $this->getDonationFormCount(),
			'donor_count'                  => $this->getDonorCount(),
			'avg_donation_amount_by_donor' => $this->getAvgDonationAmountByDonor(),
		];

		return array_merge( $data, $this->donationData );
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
		$amount = 0;

		if ( $this->donationData['revenue'] ) {
			$amount = (int) ( $this->donationData['revenue'] / $this->getDonorCount() );
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

		return (int) $formQuery->found_posts;
	}
}
