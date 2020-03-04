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

	public function get_report( $request ) {

		// Check if a cached version exists
		$cached_report = $this->get_cached_report( $request );
		if ( $cached_report !== null ) {
			// Bail and return the cached version
			return new \WP_REST_Response(
				[
					'data' => $cached_report,
				]
			);
		}

		$start = date_create( $request['start'] );
		$end   = date_create( $request['end'] );
		$diff  = date_diff( $start, $end );

		$dataset = [];

		$data = $this->get_data( $start, $end );

		// Cache the report data
		$result = $this->cache_report( $request, $data );
		$status = $this->get_give_status();

		return new \WP_REST_Response(
			[
				'data'   => $data,
				'status' => $status,
			]
		);
	}

	public function get_data( $start, $end ) {

		$this->payments = $this->get_payments( $start->format( 'Y-m-d' ), $end->format( 'Y-m-d 23:i:s' ), 'date', -1 );

		$donors = [];

		foreach ( $this->payments as $payment ) {
			if ( $payment->status === 'publish' || $payment->status === 'give_subscription' ) {
				$donors[ $payment->donor_id ]['type']      = 'donor';
				$donors[ $payment->donor_id ]['earnings']  = isset( $donors[ $payment->donor_id ]['earnings'] ) ? $donors[ $payment->donor_id ]['earnings'] += $payment->total : $payment->total;
				$donors[ $payment->donor_id ]['total']     = give_currency_filter( give_format_amount( $donors[ $payment->donor_id ]['earnings'], array( 'sanitize' => false ) ), [ 'decode_currency' => true ] );
				$donors[ $payment->donor_id ]['donations'] = isset( $donors[ $payment->donor_id ]['donations'] ) ? $donors[ $payment->donor_id ]['donations'] += 1 : 1;
				$countLabel                                = _n( 'Donation', 'Donations', $donors[ $payment->donor_id ]['donations'], 'give' );
				$donors[ $payment->donor_id ]['count']     = $donors[ $payment->donor_id ]['donations'] . ' ' . $countLabel;
				$donors[ $payment->donor_id ]['name']      = $payment->first_name . ' ' . $payment->last_name;
				$donors[ $payment->donor_id ]['email']     = $payment->email;
				$donors[ $payment->donor_id ]['image']     = give_validate_gravatar( $payment->email ) ? get_avatar_url( $payment->email, 60 ) : null;
				$donors[ $payment->donor_id ]['url']       = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . absint( $payment->donor_id ) );
			}
		}

		$sorted = usort(
			$donors,
			function ( $a, $b ) {
				if ( $a['earnings'] == $b['earnings'] ) {
					return 0;
				}
				return ( $a['earnings'] > $b['earnings'] ) ? -1 : 1;
			}
		);

		if ( $sorted === true ) {
			$donors = array_slice( $donors, 0, 25 );
			$donors = array_values( $donors );
		}

		return $donors;

	}

}
