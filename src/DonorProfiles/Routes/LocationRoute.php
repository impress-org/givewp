<?php

namespace Give\DonorProfiles\Routes;

use WP_REST_Request;
use Give\API\RestRoute;
use Give\DonorProfiles\Helpers\LocationList;

/**
 * @since 2.10.0
 */
class LocationRoute implements RestRoute {

	/** @var string */
	protected $endpoint = 'donor-profile/location';

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
						return is_user_logged_in();
					},
				],
				'schema' => [ $this, 'getSchema' ],
			]
		);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public function handleRequest( $request ) {
		return [
			'states' => LocationList::getStates(
				$request->get_param( 'countryCode' )
			),
		];
	}

	/**
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public function getSchema() {
		return [
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => 'donor-profile',
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => [
				// ...
			],
		];
	}
}
