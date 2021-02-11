<?php
namespace Give\Tracking\TrackingData;

use Give\Helpers\ArrayDataSet;
use Give\Helpers\Form\Template;
use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Traits\HasDonations;
use Give_Donors_Query;

/**
 * Class DonationFormsData
 *
 * Represents donation forms data
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class DonationFormsData implements TrackData {
	use HasDonations;

	private $formIds     = [];
	private $donationIds = [];

	/**
	 * @inheritdoc
	 */
	public function get() {
		$this->setDonationIds()->setFormIdsByDonationIds();

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
		if ( ! $this->formIds ) {
			return [];
		}

		$data = [];

		foreach ( $this->formIds as $formId ) {
			$formTemplate = Template::getActiveID( $formId );

			$temp = [
				'form_id'       => (int) $formId,
				'form_url'      => untrailingslashit( get_permalink( $formId ) ),
				'form_name'     => get_post_field( 'post_name', $formId, 'db' ),
				'form_type'     => give()->form_meta->get_meta( $formId, '_give_price_option', true ),
				'form_template' => ! $formTemplate || 'legacy' === $formTemplate ? 'legacy' : $formTemplate,
				'donor_count'   => $this->getDonorCount( $formId ),
				'revenue'       => $this->getRevenueTillNow( $formId ),
			];

			$this->addAddonsInformation( $temp, $formId );
			$data[] = $temp;
		}

		return $data;
	}

	/**
	 * Set donation ids.
	 *
	 * @since 2.10.0
	 *
	 * @return DonationFormsData
	 */
	public function setDonationIds() {
		$this->donationIds = $this->getNewDonationIdsSinceLastRequest();

		return $this;
	}

	/**
	 * Set form ids by donation ids.
	 *
	 * @return void
	 * @since 2.10.0
	 *
	 */
	public function setFormIdsByDonationIds() {
		global $wpdb;

		$donationIdsList = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote( $this->donationIds );

		$this->formIds = $wpdb->get_col(
			"
				SELECT DISTINCT meta_value
				FROM {$wpdb->donationmeta}
				WHERE meta_key='_give_payment_form_id'
				AND donation_id IN ({$donationIdsList})
				"
		);
	}

	/**
	 * Returns revenue till current date.
	 *
	 * @param int $formId
	 *
	 * @return string
	 * @since 2.10.0
	 */
	public function getRevenueTillNow( $formId ) {
		global $wpdb;

		$statues = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote(
			[
				'publish', // One time donation
				'give_subscription', // Renewal
			]
		);

		$result = (int) $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT SUM(amount)
				FROM {$wpdb->give_revenue} as r
				INNER JOIN {$wpdb->posts} as p
				INNER JOIN {$wpdb->donationmeta} as dm
				ON r.donation_id=p.id
				ON p.id=dm.id
				WHERE p.post_date<=%s
				AND post_status IN ({$statues})
				AND r.form_id=%d
				AND dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
				",
				current_time( 'mysql' ),
				$formId
			)
		);
		return $result ?: 0;
	}

	/**
	 * Get donor count.
	 *
	 * @since 2.10.0
	 *
	 * @param int $formId
	 *
	 * @return int
	 */
	private function getDonorCount( $formId ) {
		$donorQuery = new Give_Donors_Query(
			[
				'number'          => -1,
				'count'           => true,
				'donation_amount' => 0,
				'give_forms'      => [ $formId ],
			]
		);

		return (int) $donorQuery->get_donors();
	}

	/**
	 * Add addon information whether or not they active for donation form.
	 *
	 * @since 2.10.0
	 *
	 * @param array $array
	 * @param int $formId
	 */
	private function addAddonsInformation( &$array, $formId ) {
		$array = array_merge(
			$array,
			[
				'recurring_donations' => (int) apply_filters( 'give_telemetry_form_uses_addon_recurring', false, $formId ),
				'fee_recovery'        => (int) apply_filters( 'give_telemetry_form_uses_addon_fee_recovery', false, $formId ),
				'form_field_manager'  => (int) apply_filters( 'give_telemetry_form_uses_addon_form_field_manager', false, $formId ),
			]
		);
	}
}
