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

		// Setup donor query args (get sanitized start/end date from request)
		$args = [
			'number'     => 25,
			'paged'      => 1,
			'orderby'    => 'purchase_value',
			'order'      => 'DESC',
			'start_date' => $request['start'],
			'end_date'   => $request['end'],
		];

		// Get array of top 25 donors
		$donors = new \Give_Donors_Query( $args );
		$donors = $donors->get_donors();

		// Populate $list with arrays in correct shape for frontend RESTList component
		$list = [];
		foreach ( $donors as $donor ) {

			$avatar     = give_validate_gravatar( $donor->email ) ? get_avatar( $donor->email, 60 ) : null;
			$total      = give_currency_filter( give_format_amount( $donor->purchase_value, array( 'sanitize' => false ) ), [ 'decode_currency' => true ] );
			$url        = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . absint( $donor->id ) );
			$countLabel = $donor->purchase_count > 1 ? esc_html__( 'Donations', 'give' ) : esc_html__( 'Donation', 'give' );

			$list[] = [
				'type'  => 'donor',
				'name'  => $donor->name,
				'url'   => $url,
				'count' => "{$donor->purchase_count} {$countLabel}",
				'total' => $total,
				'image' => $avatar,
				'email' => $donor->email,
			];
		}

		// Return $list of donors for RESTList component
		return new \WP_REST_Response(
			[
				'data' => $list,
			]
		);
	}
}
