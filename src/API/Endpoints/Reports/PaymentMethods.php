<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class PaymentMethods extends Endpoint {

	public function __construct() {
		$this->endpoint = 'payment-methods';
	}

	public function get_report( $request ) {

		// Use give_count_payments logic to get payments
		$gateways = give_get_payment_gateways();
		$stats    = new \Give_Payment_Stats();

		$gatewaysArr = [];

		foreach ( $gateways as $gateway_id => $gateway ) {
			$gatewaysArr[] = [
				'admin_label' => $gateway['admin_label'],
				'count'       => $stats->get_sales( 0, date( $request['start'] ), date( $request['end'] ), $gateway_id ),
				'amount'      => $stats->get_earnings( 0, date( $request['start'] ), date( $request['end'] ), $gateway_id ),
			];
		}
		$sorted = usort(
			$gatewaysArr,
			function ( $a, $b ) {
				if ( $a['amount'] == $b['amount'] ) {
					return 0;
				}
				return ( $a['amount'] > $b['amount'] ) ? -1 : 1;
			}
		);

		$labels   = [];
		$data     = [];
		$tooltips = [];

		if ( $sorted == true ) {
			$gatewaysArr = array_slice( $gatewaysArr, 0, 5 );
			foreach ( $gatewaysArr as $gateway ) {
				$labels[]   = $gateway['admin_label'];
				$data[]     = $gateway['amount'];
				$tooltips[] = [
					'title'  => give_currency_filter( give_format_amount( $gateway['amount'] ), [ 'decode_currency' => true ] ),
					'body'   => $gateway['count'] . ' ' . __( 'Payments', 'give' ),
					'footer' => $gateway['admin_label'],
				];
			}
		}
		$status = $this->get_give_status();

		return new \WP_REST_Response(
			[
				'data'   => [
					'labels'   => $labels,
					'datasets' => [
						[
							'data'     => $data,
							'tooltips' => $tooltips,
						],
					],
				],
				'status' => $status,
			]
		);
	}
}
