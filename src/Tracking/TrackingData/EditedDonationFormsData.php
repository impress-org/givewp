<?php

namespace Give\Tracking\TrackingData;

use Give\Helpers\ArrayDataSet;

/**
 * Class EditedDonationFormsData
 *
 * @package Give\Tracking\TrackingData
 * @unreleased
 */
class EditedDonationFormsData extends DonationFormsData {
	/**
	 * @inheritdoc
	 */
	public function get() {
		$this->setFormIds();

		if ( ! $this->formIds ) {
			return [];
		}

		$this->setDonationIds();

		$this->setRevenues()
			 ->setDonorCounts();

		return $this->getData();
	}

	/**
	 * Set recently edited form ids.
	 *
	 * @since 2.10.0
	 * @return self
	 */
	protected function setFormIds() {
		global $wpdb;
		$lastRequestTime = $this->trackEvents->getRequestTime();

		$this->formIds = $wpdb->get_col(
			"
			SELECT ID
			FROM {$wpdb->posts} as p
			WHERE post_status='publish'
				AND post_type='give_forms'
				AND post_modified>='{$lastRequestTime}'
			"
		);

		return $this;
	}

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

		$formIdsList       = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote( $this->formIds );
		$this->donationIds = $wpdb->get_col(
			"
			SELECT ID
			FROM {$wpdb->posts} as p
				INNER JOIN {$wpdb->donationmeta} as dm ON p.id=dm.donation_id
				INNER JOIN {$wpdb->donationmeta} as dm2 ON p.id=dm2.donation_id
			WHERE post_status IN ({$statues})
				AND post_type='give_payment'
				AND dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
				AND dm2.meta_key='_give_payment_form_id'
				AND dm2.meta_value IN ({$formIdsList})
			"
		);

		return $this;
	}
}
