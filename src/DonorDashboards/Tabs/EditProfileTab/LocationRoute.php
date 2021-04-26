<?php

namespace Give\DonorDashboards\Tabs\EditProfileTab;

use WP_REST_Request;
use Give\DonorDashboards\Tabs\Contracts\Route as RouteAbstract;
use Give\DonorDashboards\Helpers\LocationList;

/**
 * @since 2.10.0
 */
class LocationRoute extends RouteAbstract {

	/** @var string */
	public function endpoint() {
		return 'location';
	}

	public function args() {
		return [
			'countryCode' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public function handleRequest( WP_REST_Request $request ) {
		return [
			'states' => LocationList::getStates(
				$request->get_param( 'countryCode' )
			),
		];
	}
}
