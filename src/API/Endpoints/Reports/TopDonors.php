<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TopDonors extends Endpoint {

	public function __construct() {
		$this->endpoint = 'top-donors';
	}

	public function get_report( $request ) {

		$args = [
			'number'     => 10,
			'paged'      => 1,
			'orderby'    => 'purchase_value',
			'order'      => 'DESC',
			'start_date' => $request['start'],
			'end_date'   => $request['end'],
		];

		$donors = new \Give_Donors_Query( $args );
		$donors = $donors->get_donors();

		$list = [];

		foreach ( $donors as $donor ) {

			$avatar = give_validate_gravatar( $donor->email ) ? get_avatar( $donor->email, 60 ) : null;

			$item = [
				'type'  => 'donor',
				'name'  => $donor->name,
				'count' => $donor->purchase_count,
				'total' => $donor->purchase_value,
				'image' => $avatar,
				'email' => $donor->email,
			];
			array_push( $list, $item );
		}

		return new \WP_REST_Response(
			[
				'donors' => $donors,
				'data'   => $list,
			]
		);
	}
}
