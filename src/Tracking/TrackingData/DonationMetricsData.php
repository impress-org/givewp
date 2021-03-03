<?php

namespace Give\Tracking\TrackingData;

use Give\Helpers\ArrayDataSet;
use Give\Tracking\Contracts\TrackData;

/**
 * Class DonationMetricsData
 * @package Give\Tracking\TrackingData
 *
 * @unreleased
 */
class DonationMetricsData implements TrackData {
	private $donationData = [];
	private $donationIds  = [];
	private $donorCount   = 0;
	private $formCount    = 0;

	/**
	 * @inheritdoc
	 * @return array|void
	 */
	public function get() {
		$this->setDonationIds();

		if ( ! $this->donationIds ) {
			return [];
		}

		$this->donorCount   = $this->getDonorCount();
		$this->formCount    = $this->getDonationFormCount();
		$this->donationData = ( new DonationData() )->get();

		$data = [
			'form_count'                   => $this->formCount,
			'donor_count'                  => $this->donorCount,
			'avg_donation_amount_by_donor' => $this->getAvgDonationAmountByDonor(),
		];

		return array_merge( $data, $this->donationData );
	}

	/**
	 * Set donation ids.
	 *
	 * @unreleased
	 */
	private function setDonationIds() {
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
				AND dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
			"
		);
	}

	/**
	 * Returns donor count which donated greater then zero
	 *
	 * @since 2.10.0
	 * @return int
	 */
	private function getDonorCount() {
		global $wpdb;

		$donationIdsList = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote( $this->donationIds );
		$donorCount      = $wpdb->get_var(
			"
			SELECT COUNT(DISTINCT dm.meta_value)
			FROM {$wpdb->donationmeta} as dm
				INNER JOIN {$wpdb->posts} as p ON dm.donation_id = p.ID
				INNER JOIN {$wpdb->donors} as donor ON dm.meta_value = donor.id
			WHERE p.ID IN ({$donationIdsList})
				AND dm.meta_key='_give_payment_donor_id'
				AND donor.purchase_value > 0
			"
		);

		return (int) $donorCount;
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
			$amount = (int) ( $this->donationData['revenue'] / $this->donorCount );
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
		global $wpdb;

		$donationIdsList = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote( $this->donationIds );
		$formCount       = $wpdb->get_var(
			"
			SELECT COUNT(DISTINCT dm.meta_value)
			FROM {$wpdb->donationmeta} as dm
				INNER JOIN {$wpdb->posts} as p ON dm.donation_id = p.ID
			WHERE p.ID IN ({$donationIdsList})
				AND dm.meta_key='_give_payment_form_id'
			"
		);

		return (int) $formCount;
	}
}
