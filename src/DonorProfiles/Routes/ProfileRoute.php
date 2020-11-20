<?php

namespace Give\DonorProfiles\Routes;

use WP_REST_Request;
use Give\API\RestRoute;
use \Give_Donor as Donor;
use Give\DonorProfiles\Model as Model;

/**
 * @since 2.10.0
 */
class ProfileRoute implements RestRoute {

	/** @var string */
	protected $endpoint = 'donor-profile/profile';

	/**
	 * @inheritDoc
	 */
	public function registerRoute() {
		register_rest_route(
			'give-api/v2',
			$this->endpoint,
			[
				[
					'methods'             => 'Get',
					'callback'            => [ $this, 'handleGetRequest' ],
					'permission_callback' => function() {
						return is_user_logged_in();
					},
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'handlePostRequest' ],
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
	public function handleRequest( WP_REST_Request $request ) {
		return $this->getProfile();
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public function handlePostRequest( WP_REST_Request $request ) {
		return $this->updateProfile( json_decode( $request->get_param( 'data' ) ) );
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

	/**
	 * @return array
	 *
	 * @since 2.10.0
	 */
	protected function getProfile() {

		$donorId = get_current_user_id();
		$donor   = new Donor( $donorId );

		return $donor;
	}

	/**
	 * @return array
	 *
	 * @since 2.10.0
	 */
	protected function updateProfile( $data ) {

		error_log( $data );

		$donorId = get_current_user_id();
		$donor   = new Donor( $donorId );
		$updated = true; //$donor->update( $data );

		return [
			'updated' => $updated ? true : false,
			'donor'   => $donor,
		];
	}
}
