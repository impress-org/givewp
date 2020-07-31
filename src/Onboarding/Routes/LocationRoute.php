<?php

namespace Give\Onboarding\Routes;

use WP_REST_Request;
use Give\API\RestRoute;
use Give\Onboarding\Helpers\FormatList;
use Give\Onboarding\Helpers\CountryCode;

class LocationRoute implements RestRoute {

	protected $endpoint = 'onboarding/location';

	public function handleRequest( WP_REST_Request $request ) {

		$countryCode = $request->get_param( 'countryCode' );
		$statesList  = give_get_states( $countryCode );

		$statesList[''] = '-';

		return [
			'states' => FormatList::fromKeyValue( $statesList ),
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
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => function() {
						return current_user_can( 'manage_options' );
					},
					'args'                => [
						'countryCode' => [
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => 'give_get_country_name_by_key',
							'sanitize_callback' => 'sanitize_text_field',
						],
					],
				],
				// Register our schema callback.
				// 'schema' => [ $this, 'getSchema' ],
			]
		);
	}
}
