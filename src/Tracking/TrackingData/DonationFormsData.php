<?php
namespace Give\Tracking\TrackingData;

use Give\Helpers\ArrayDataSet;
use Give\Helpers\Form\Template;
use Give\Helpers\Utils;
use Give\Tracking\Contracts\TrackData;
use Give\ValueObjects\Money;
use Give_Donors_Query;

/**
 * Class DonationFormsData
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class DonationFormsData implements TrackData {
	private $formIds     = [];
	private $donationIds = [];

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
		if ( ! $this->formIds ) {
			return [];
		}

		$data          = [];
		$defaultValues = [
			'donorCount'         => 0,
			'revenue'            => 0,
			'isRecurring'        => 0,
			'isFeeRecovery'      => 0,
			'isFormFieldManager' => 0,
		];

		foreach ( $this->formIds as $formId ) {
			$recurringType    = give()->form_meta->get_meta( $formId, '_give_recurring', true );
			$formTemplate     = Template::getActiveID( $formId );
			$customFormFields = give()->form_meta->get_meta( $formId, 'give-form-fields', true );

			$data[ $formId ]['slug']         = get_post_field( 'post_name', $formId, 'db' );
			$data[ $formId ]['formType']     = give()->form_meta->get_meta( $formId, '_give_price_option', true );
			$data[ $formId ]['formTemplate'] = ! $formTemplate || 'legacy' === $formTemplate ? 'legacy' : $formTemplate;
			$data[ $formId ]['donorCount']   = $this->getDonorCount( $formId );
			$data[ $formId ]['revenue']      = $this->getRevenueTillNow( $formId );
			$data[ $formId ]['isRecurring']  = (int) ( $recurringType && $recurringType !== 'no' );
			// $data[$formId]['isFeeRecovery'] = absint( $formsMetaData[$formId]['_give_recurring'] !== 'no' );
			$data[ $formId ]['isFormFieldManager'] = (int) ! empty( $customFormFields );

			$data[ $formId ] = wp_parse_args( $data[ $formId ], $defaultValues );
		}

		return $data;
	}

	/**
	 * Set donation ids.
	 *
	 * @since 2.10.0
	 *
	 * @param  array  $donationIds
	 *
	 * @return DonationFormsData
	 */
	public function setDonationIds( $donationIds ) {
		$this->donationIds = $donationIds;

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

		$currency = give_get_option( 'currency' );
		$statues  = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote(
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
		return $result ? Money::ofMinor( $result, $currency )->getAmount() : '';
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
}
