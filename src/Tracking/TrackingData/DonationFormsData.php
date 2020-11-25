<?php
namespace Give\Tracking\TrackingData;

use Give\Helpers\ArrayDataSet;
use Give\Tracking\Contracts\TrackData;

/**
 * Class DonationFormsData
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class DonationFormsData implements TrackData {
	private $formIds     = [];
	private $donationIds = [];
	private $donationIdsList;
	private $formIdsList;

	/**
	 * @inheritdoc
	 */
	public function get() {
		if ( ! $this->formIds ) {
			return [];
		}

		return $this->getData();
	}

	/**
	 * Get forms data.
	 *
	 * @since 2.10.0
	 * @return array
	 */
	private function getData() {
		global $wpdb;

		if ( ! $this->formIds ) {
			return [];
		}

		$donorCount = $wpdb->get_results(
			"
			SELECT DISTINCT(dm1.meta_value),
			FROM {$wpdb->donationmeta} as dm1
			INNER JOIN {$wpdb->donationmeta} as dm2
			ON dm2.donation_id=dm1.donation_id
			WHERE donation_id in ({$this->donationIdsList})
			AND meta_key='_give_payment_donor_id'
			",
			ARRAY_N
		);
	}

	/**
	 * Set form ids by donation ids.
	 *
	 * @since 2.10.0
	 * @param array $donationIds
	 *
	 * @return array
	 */
	public function setFormIdsByDonationIds( $donationIds ) {
		global $wpdb;

		$this->donationIds = $donationIds;
		$donationIdsList   = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote( $this->donationIds );

		return (array) $wpdb->get_results(
			"
				SELECT meta_value
				FROM {$wpdb->donationmeta}
				WHERE meta_value='_give_payment_form_id'
				AND donation_id IN ({$donationIdsList})
				"
		);
	}
}
