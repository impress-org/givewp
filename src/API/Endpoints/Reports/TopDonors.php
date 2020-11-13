<?php

/**
 * Top Donors endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TopDonors extends Endpoint {

	public function __construct() {
		$this->endpoint = 'top-donors';
	}

	public function getReport( $request ) {
		$start = date_create( $request->get_param( 'start' ) );
		$end   = date_create( $request->get_param( 'end' ) );

		return $this->get_data( $start, $end );
	}

	public function get_data( $start, $end ) {

		global $wpdb;
		$donors = $wpdb->get_results(
			"
			SELECT
				SUM( donationAmount.meta_value ) as earnings,
				COUNT( donation.ID ) as donations,
				donorID.meta_value as donor_id,
				donor.name,
				donor.email
			FROM {$wpdb->prefix}posts as donation
			JOIN {$wpdb->prefix}give_donationmeta as donationMode
				ON donation.ID = donationMode.donation_id
				AND donationMode.meta_key = '_give_payment_mode'
				AND donationMode.meta_value = 'test'
			JOIN {$wpdb->prefix}give_donationmeta as donationAmount
				ON donation.ID = donationAmount.donation_id
				AND donationAmount.meta_key = '_give_payment_total'
			JOIN {$wpdb->prefix}give_donationmeta as donorID
				ON donation.ID = donorID.donation_id
				AND donorID.meta_key = '_give_payment_donor_id'
			JOIN {$wpdb->prefix}give_donors as donor
				ON donorID.meta_value = donor.id
			WHERE donation.post_type = 'give_payment'
				AND donation.post_date BETWEEN '2020-11-6' AND '2020-11-13'
			GROUP BY donor_id
			ORDER BY earnings DESC
		",
			ARRAY_A
		);

		$donors = array_map(
			function( $donor ) {
				$donor['type']  = 'donor';
				$donor['total'] = give_currency_filter(
					give_format_amount( $donor['earnings'], [ 'sanitize' => false ] ),
					[
						'currency_code'   => $this->currency,
						'decode_currency' => true,
						'sanitize'        => false,
					]
				);
				$donor['image'] = give_validate_gravatar( $donor['email'] ) ? get_avatar_url( $donor['email'], 60 ) : null;
				$donor['url']   = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . absint( $donor['donor_id'] ) );
				return $donor;
			},
			$donors
		);

		return $donors;

	}

}
