<?php

namespace Give\Onboarding\Routes;

use WP_REST_Request;
use Give\API\RestRoute;

class SettingsRoute implements RestRoute {

	protected $endpoint = 'onboarding/settings';

	public function handleRequest( WP_REST_Request $request ) {
		return [
			'data' => [
				'setting' => $request->get_param( 'setting' ),
				'value'   => json_decode( $request->get_param( 'value' ) ),
			],
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
					'methods'             => 'POST',
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => function() {
						return current_user_can( 'manage_options' );
					},
					'args'                => [
						'setting' => [
							'type'              => 'string',
							'required'          => true,
							// 'validate_callback' => [ $this, 'validateSetting' ],
							'sanitize_callback' => 'sanitize_text_field',
						],
						'value'   => [
							'type'              => 'string',
							'required'          => false,
							// 'validate_callback' => [ $this, 'validateValue' ],
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
