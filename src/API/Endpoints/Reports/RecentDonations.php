<?php

/**
 * Recent Donations endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class RecentDonations extends Endpoint {

	public function __construct() {
		$this->endpoint = 'recent-donations';
	}

	public function get_report( $request ) {
		// Setup donation query args (get sanitized start/end date from request)
		$args = array(
			'number'     => 50,
			'paged'      => 1,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'start_date' => $request->get_param( 'start' ),
			'end_date'   => $request->get_param( 'end' ),
		);

		// Get array of 50 recent donations
		$donations = new \Give_Payments_Query( $args );
		$donations = $donations->get_payments();

		// Populate $list with arrays in correct shape for frontend RESTList component
		$data = array();
		foreach ( $donations as $donation ) {

			$donation = new \Give_Payment( $donation->ID );

			$amount = give_currency_symbol( $donation->currency, true ) . give_format_amount( $donation->total, array( 'sanitize' => false ) );
			$status = null;
			switch ( $donation->status ) {
				case 'publish':
					$meta   = $donation->payment_meta;
					$status = isset( $meta['_give_is_donation_recurring'] ) && $meta['_give_is_donation_recurring'] ? 'first_renewal' : 'completed';
					break;
				case 'give_subscription':
					$status = 'renewal';
					break;
				default:
					$status = $donation->status;
			}
			$url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . absint( $donation->ID ) );

			$data[] = [
				'type'     => 'donation',
				'donation' => $donation,
				'status'   => $status,
				'amount'   => $amount,
				'url'      => $url,
				'time'     => $donation->date,
				'donor'    => array(
					'name' => "{$donation->first_name} {$donation->last_name}",
					'id'   => $donation->donor_id,
				),
				'source'   => $donation->form_title,
			];
		}

		// Return $list of donations for RESTList component
		return $data;
	}
}
