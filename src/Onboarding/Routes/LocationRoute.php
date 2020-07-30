<?php

namespace Give\Onboarding\Routes;

use WP_REST_Request;
use Give\API\RestRoute;

class LocationRoute implements RestRoute {

	protected $endpoint = 'onboarding/location';

	public function handleRequest( WP_REST_Request $request ) {

		$countryCode = $request->get_param( 'countryCode' );
		$statesList  = give_get_states( $countryCode );

		$statesList[''] = '-';

		return [
			'states' => array_map(
				function( $value, $label ) {
					return [
						'value' => $value,
						'label' => $label,
					];
				},
				array_keys( $statesList ),
				$statesList
			),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function registerRoute() {
		register_rest_route(
			'give-api/v2',
			$this->endpoint,
			[
				// Here we register the readable endpoint
				[
					'methods'  => 'GET',
					'callback' => [ $this, 'handleRequest' ],
					// 'permission_callback' => [ $this, 'permissionsCheck' ],
					'args'     => [
						'countryCode' => [
							'type'     => 'string',
							'required' => true,
							// 'validate_callback' => [ $this, 'validateSetting' ],
							// 'sanitize_callback' => [ $this, 'sanitizeSetting' ],
						],
					],
				],
				// Register our schema callback.
				// 'schema' => [ $this, 'getReportSchema' ],
			]
		);
	}
}
