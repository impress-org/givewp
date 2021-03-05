<?php
namespace Give\Tracking\TrackingData;

use Give\Framework\Database\DB;
use Give\Helpers\ArrayDataSet;
use Give\Helpers\Form\Template;
use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Helpers\DonationStatuses;
use Give\Tracking\Repositories\TrackEvents;

/**
 * Class DonationFormsData
 *
 * Represents donation forms data
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class DonationFormsData implements TrackData {
	protected $formIds         = [];
	protected $formRevenues    = [];
	protected $formDonorCounts = [];

	/**
	 * @var TrackEvents
	 */
	protected $trackEvents;

	/**
	 * DonationFormsData constructor.
	 *
	 * @param  TrackEvents  $trackEvents
	 */
	public function __construct( TrackEvents $trackEvents ) {
		$this->trackEvents = $trackEvents;
	}

	/**
	 * @inheritdoc
	 */
	public function get() {
		$this->setFormIds();

		if ( ! $this->formIds ) {
			return [];
		}

		$this->setRevenues()
			 ->setDonorCounts();

		return $this->getData();
	}

	/**
	 * Get forms data.
	 *
	 * @since 2.10.0
	 * @return array
	 */
	protected function getData() {
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
				'donor_count'   => $this->formDonorCounts[ $formId ],
				'revenue'       => $this->formRevenues[ $formId ],
			];

			$this->addAddonsInformation( $temp, $formId );
			$data[] = $temp;
		}

		return $data;
	}

	/**
	 * Set form ids.
	 *
	 * @since 2.10.0
	 * @return self
	 */
	protected function setFormIds() {
		global $wpdb;

		$statues = DonationStatuses::getCompletedDonationsStatues( true );
		$time    = $this->trackEvents->getRequestTime();

		$this->formIds = DB::get_col(
			"
			SELECT DISTINCT dm.meta_value
			FROM {$wpdb->donationmeta} as dm
				INNER JOIN {$wpdb->posts} as p ON dm.donation_id = p.ID
				INNER JOIN {$wpdb->donationmeta} as dm2 ON dm.donation_id = dm2.donation_id
			WHERE p.post_status IN ({$statues})
			  	AND p.post_date>='{$time}'
			  	AND p.post_type='give_payment'
				AND dm2.meta_key='_give_payment_mode'
				AND dm2.meta_value='live'
				AND dm.meta_key='_give_payment_form_id'
			"
		);

		return $this;
	}

	/**
	 * Set forms revenues.
	 *
	 * @since 2.10.0
	 * @return self
	 */
	protected function setRevenues() {
		global $wpdb;

		$formIds       = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote( $this->formIds );
		$defaultResult = array_combine(
			$this->formIds,
			array_fill( 0, count( $this->formIds ), 0 ) // Set default revenue to 0
		);

		$result = DB::get_results(
			"
			SELECT SUM(r.amount) as amount, r.form_id
			FROM {$wpdb->give_revenue} as r
				INNER JOIN {$wpdb->donationmeta} as dm ON r.donation_id = dm.donation_id
			WHERE dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
				AND r.form_id IN ({$formIds})
			GROUP BY form_id
			",
			ARRAY_A
		);

		if ( $result ) {
			$result = array_map(
				'absint',
				array_combine(
					wp_list_pluck( $result, 'form_id' ),
					wp_list_pluck( $result, 'amount' )
				)
			);
		}

		$this->formRevenues = array_replace( $defaultResult, $result );

		return $this;
	}

	/**
	 * Set forms revenues till current date.
	 *
	 * @since 2.10.0
	 *
	 * @return self
	 */
	protected function setDonorCounts() {
		global $wpdb;

		$formIds       = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote( $this->formIds );
		$statues       = DonationStatuses::getCompletedDonationsStatues( true );
		$defaultResult = array_combine(
			$this->formIds,
			array_fill( 0, count( $this->formIds ), 0 ) // Set default donor count to 0
		);

		$result = DB::get_results(
			"
			SELECT COUNT(DISTINCT dm2.meta_value) as donor_count, dm.meta_value as form_id
			FROM {$wpdb->donationmeta} as dm
			    INNER JOIN {$wpdb->posts} as p ON donation_id = p.ID
				INNER JOIN {$wpdb->donationmeta} as dm2 ON dm.donation_id = dm2.donation_id
				INNER JOIN {$wpdb->donors} as donor ON dm2.meta_value = donor.id
				INNER JOIN {$wpdb->donationmeta} as dm3 ON dm.donation_id = dm3.donation_id
			WHERE p.post_status IN ({$statues})
			  	AND dm3.meta_key='_give_payment_mode'
				AND dm3.meta_value='live'
			    AND dm.meta_key='_give_payment_form_id'
				AND dm.meta_value IN ({$formIds})
				AND dm2.meta_key='_give_payment_donor_id'
				AND donor.purchase_value > 0
			GROUP BY dm.meta_value
			",
			ARRAY_A
		);

		if ( $result ) {
			$result = array_map(
				'absint',
				array_combine(
					wp_list_pluck( $result, 'form_id' ),
					wp_list_pluck( $result, 'donor_count' )
				)
			);
		}

		$this->formDonorCounts = array_replace( $defaultResult, $result );

		return $this;
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
