<?php

namespace Give\DonorProfiles\Routes;

use WP_REST_Request;
use Give\API\RestRoute;
use Give\DonorProfiles\Profile as Profile;

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
					'methods'             => 'POST',
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => function() {
						return true; //is_user_logged_in();
					},
				],
				'schema' => [ $this, 'getSchema' ],
				'args'   => [
					'data' => [
						'type'              => 'string',
						'required'          => false,
						// 'validate_callback' => [ $this, 'validateValue' ],
						'sanitize_callback' => 'sanitize_text_field',
					],
					'id'   => [
						'type'     => 'int',
						'required' => true,
						// 'validate_callback' => [ $this, 'validateValue' ],
						// 'sanitize_callback' => 'sanitize_text_field',
					],
				],
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
		return $this->updateProfile( json_decode( $request->get_param( 'data' ) ), $request->get_param( 'id' ) );
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
		$profile = new Profile( $donorId );
		return $profile->getProfileData();
	}

	/**
	 * @return array
	 *
	 * @since 2.10.0
	 */
	protected function updateProfile( $data, $id ) {
		$profile = new Profile( $id );
		$profile->update( $data );
		return [
			'profile' => $profile->getProfileData(),
		];
	}
}
