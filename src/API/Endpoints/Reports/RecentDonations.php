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

	public function getReport( $request ) {

		$paymentObjects = $this->getPayments( $request->get_param( 'start' ), $request->get_param( 'end' ), 'date', 50 );

		// Populate $list with arrays in correct shape for frontend RESTList component
		$data = [];
		foreach ( $paymentObjects as $paymentObject ) {
			$amount = give_currency_symbol( $paymentObject->currency, true ) . give_format_amount( $paymentObject->total, [ 'sanitize' => false ] );
			$status = null;
			switch ( $paymentObject->status ) {
				case 'publish':
					$meta   = $paymentObject->payment_meta;
					$status = isset( $meta['_give_is_donation_recurring'] ) && $meta['_give_is_donation_recurring'] ? 'first_renewal' : 'completed';
					break;
				case 'give_subscription':
					$status = 'renewal';
					break;
				default:
					$status = $paymentObject->status;
			}
			$url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . absint( $paymentObject->ID ) );

			$data[] = [
				'type'     => 'donation',
				'donation' => $paymentObject,
				'status'   => $status,
				'amount'   => $amount,
				'url'      => $url,
				'time'     => $paymentObject->date,
				'donor'    => [
					'name' => "{$paymentObject->first_name} {$paymentObject->last_name}",
					'id'   => $paymentObject->donor_id,
				],
				'source'   => $paymentObject->form_title,
			];
		}

		// Return $list of donations for RESTList component
		return $data;
	}
}
