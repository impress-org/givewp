<?php

namespace Give\Onboarding\Routes;

use WP_REST_Request;
use Give\API\RestRoute;
use Give\Onboarding\Helpers\FormatList;
use Give\Onboarding\Helpers\CountryCode;
use Give\Onboarding\Helpers\LocationList;

/**
 * @since 2.8.0
 */
class LocationRoute implements RestRoute {

	/** @var string */
	protected $endpoint = 'onboarding/location';

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.8.0
	 */
	public function handleRequest( WP_REST_Request $request ) {
		return [
			'states' => LocationList::getStates(
				$request->get_param( 'countryCode' )
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
				'schema' => [ $this, 'getSchema' ],
			]
		);
	}

	/**
	 * @return array
	 *
	 * @since 2.8.0
	 */
	public function getSchema() {
		return [
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => 'onboarding',
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => [
				'countryCode' => [
					'description' => esc_html__( 'A short alphabetic geographical code representing a country.', 'give' ),
					'type'        => 'string',
				],
			],
		];
	}
}
