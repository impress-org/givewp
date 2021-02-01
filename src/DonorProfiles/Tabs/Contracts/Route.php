<?php

namespace Give\DonorProfiles\Tabs\Contracts;

use WP_REST_Request;
use WP_REST_Response;
use Give\API\RestRoute;

/**
 * @since 2.11.0
 */
abstract class Route implements RestRoute {

	/**
	 * Returns string to complete Route endpoint
	 * Full route will be donor-profile/{endpoint}
	 *
	 * @return string
	 *
	 * @since 2.11.0
	 */
	abstract public function endpoint();

	/**
	 * Returns arguments for Route
	 * For more information, see: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/#arguments
	 *
	 * @return array
	 *
	 * @since 2.11.0
	 */
	abstract public function args();

	/**
	 * Handles route request, and returns response
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 *
	 * @since 2.11.0
	 */
	abstract public function handleRequest( WP_REST_Request $request );

	/** @var string */
	protected $root = 'donor-profile/';

	/**
	 * @inheritDoc
	 */
	public function registerRoute() {
		register_rest_route(
			'give-api/v2',
			"{$this->root}{$this->endpoint()}",
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => function() {
						return is_user_logged_in();
					},
				],
				'args' => $this->args(),
			]
		);
	}
}
