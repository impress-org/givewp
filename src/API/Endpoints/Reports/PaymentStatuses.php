<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class PaymentStatuses extends Endpoint {

	public function __construct() {
		$this->endpoint = 'payment-statuses';
	}

	public function get_report( $request ) {

		$start = date_create( $request->get_param( 'start' ) );
		$end   = date_create( $request->get_param( 'end' ) );

		$gateways = give_get_payment_gateways();
		unset( $gateways['manual'] );
		$gateway = $this->testMode ? 'manual' : array_keys( $gateways );

		// Setup args for give_count_payments
		$args = array(
			'start-date' => $start->format( 'Y-m-d H:i:s' ),
			'end-date'   => $end->format( 'Y-m-d H:i:s' ),
			'gateway'    => $gateway,
		);

		// Use give_count_payments logic to get payments
		$payments  = give_count_payments( $args );
		$completed = property_exists( $payments, 'give_subscription' ) ? $payments->publish + $payments->give_subscription : $payments->publish;

		return array(
			'labels'   => array(
				'Completed',
				'Pending',
				'Refunded',
				'Abandoned',
				'Cancelled',
				'Failed',
			),
			'datasets' => array(
				array(
					'data'     => array(
						$completed,
						$payments->pending,
						$payments->refunded,
						$payments->abandoned,
						$payments->cancelled,
						$payments->failed,
					),
					'tooltips' => array(
						array(
							'title'  => $completed . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Completed', 'give' ),
							'footer' => '',
						),
						array(
							'title'  => $payments->pending . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Pending', 'give' ),
							'footer' => '',
						),
						array(
							'title'  => $payments->refunded . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Refunded', 'give' ),
							'footer' => '',
						),
						array(
							'title'  => $payments->abandoned . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Abandoned', 'give' ),
							'footer' => '',
						),
						array(
							'title'  => $payments->cancelled . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Cancelled', 'give' ),
							'footer' => '',
						),
						array(
							'title'  => $payments->failed . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Failed', 'give' ),
							'footer' => '',
						),
					),
				),
			),
		);
	}
}
