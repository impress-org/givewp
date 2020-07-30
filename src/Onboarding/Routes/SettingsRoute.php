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
				// Here we register the readable endpoint
				[
					'methods'  => 'POST',
					'callback' => [ $this, 'handleRequest' ],
					// 'permission_callback' => [ $this, 'permissionsCheck' ],
					'args'     => [
						'setting' => [
							'type'     => 'string',
							'required' => true,
							// 'validate_callback' => [ $this, 'validateSetting' ],
							// 'sanitize_callback' => [ $this, 'sanitizeSetting' ],
						],
						'value'   => [
							'type'     => 'string',
							'required' => false,
							// 'validate_callback' => [ $this, 'validateValue' ],
							// 'sanitize_callback' => [ $this, 'sanitizeValue' ],
						],
					],
				],
				// Register our schema callback.
				// 'schema' => [ $this, 'getReportSchema' ],
			]
		);
	}
}
