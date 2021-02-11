<?php
namespace Give\Tracking\TrackingData;

use Give\Helpers\ArrayDataSet;
use Give\Helpers\Form\Template;
use Give\Helpers\Utils;
use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Traits\HasDonations;
use Give\ValueObjects\Money;
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

			$data[]['form_id']       = $formId;
			$data[]['form_name']     = get_post_field( 'post_name', $formId, 'db' );
			$data[]['form_type']     = give()->form_meta->get_meta( $formId, '_give_price_option', true );
			$data[]['form_template'] = ! $formTemplate || 'legacy' === $formTemplate ? 'legacy' : $formTemplate;
			$data[]['donor_count']   = $this->getDonorCount( $formId );
			$data[]['revenue']       = $this->getRevenueTillNow( $formId );

			$this->addAddonsInformation( $data, $formId );
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

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT SUM(amount)
				FROM {$wpdb->give_revenue} as r
				INNER JOIN {$wpdb->posts} as p
				ON r.donation_id=p.id
				WHERE p.post_date<=%s
				AND post_status IN ({$statues})
				AND r.form_id=%d
				",
				current_time( 'mysql' ),
				$formId
			)
		);
		return $result ?: '';
	}

	/**
	 * Get donor count.
	 *
	 * @since 2.10.0
	 *
	 * @param int $formId
	 *
	 * @return array|object|string|null
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

		return $donorQuery->get_donors();
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
		$array['recurring_donation'] = (int) apply_filters( 'give_telemetry_form_uses_addon_recurring', false, $formId );
		$array['fee_recovery']       = (int) apply_filters( 'give_telemetry_form_uses_addon_fee_recovery', false, $formId );
		$array['form_field_manager'] = (int) apply_filters( 'give_telemetry_form_uses_addon_form_field_manager', false, $formId );
	}
}
