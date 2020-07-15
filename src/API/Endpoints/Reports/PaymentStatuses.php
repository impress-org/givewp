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

	public function getReport( $request ) {

		$start = date_create( $request->get_param( 'start' ) );
		$end   = date_create( $request->get_param( 'end' ) );

		$gatewayObjects        = give_get_payment_gateways();
		$paymentModeKeyCompare = '!=';

		if ( $this->testMode === false ) {
			unset( $gatewayObjects['manual'] );
			$paymentModeKeyCompare = '=';
		}

		$gateway = array_keys( $gatewayObjects );

		$args = [
			'number'     => -1,
			'paged'      => 1,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'start_date' => $request->get_param( 'start' ),
			'end_date'   => $request->get_param( 'end' ),
			'gateway'    => $gateway,
			'meta_query' => [
				[
					'key'     => '_give_payment_currency',
					'value'   => $this->currency,
					'compare' => '=',
				],
				[
					'key'     => '_give_payment_mode',
					'value'   => 'live',
					'compare' => $paymentModeKeyCompare,
				],
			],
		];

		// Use give_count_payments logic to get payments
		$payments  = give_count_payments( $args );
		$completed = property_exists( $payments, 'give_subscription' ) ? $payments->publish + $payments->give_subscription : $payments->publish;

		return [
			'labels'   => [
				'Completed',
				'Pending',
				'Refunded',
				'Abandoned',
				'Cancelled',
				'Failed',
			],
			'datasets' => [
				[
					'data'     => [
						$completed,
						$payments->pending,
						$payments->refunded,
						$payments->abandoned,
						$payments->cancelled,
						$payments->failed,
					],
					'tooltips' => [
						[
							'title'  => $completed . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Completed', 'give' ),
							'footer' => '',
						],
						[
							'title'  => $payments->pending . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Pending', 'give' ),
							'footer' => '',
						],
						[
							'title'  => $payments->refunded . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Refunded', 'give' ),
							'footer' => '',
						],
						[
							'title'  => $payments->abandoned . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Abandoned', 'give' ),
							'footer' => '',
						],
						[
							'title'  => $payments->cancelled . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Cancelled', 'give' ),
							'footer' => '',
						],
						[
							'title'  => $payments->failed . ' ' . __( 'Payments', 'give' ),
							'body'   => __( 'Failed', 'give' ),
							'footer' => '',
						],
					],
				],
			],
		];
	}
}
