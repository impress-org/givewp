<?php

namespace Give\DonorProfiles\Routes;

use WP_REST_Request;
use Give\API\RestRoute;
use Give\DonorProfiles\Profile as Profile;
use Give\DonorProfiles\Helpers\SanitizeProfileData as SanitizeHelper;

/**
 * @since 2.11.0
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
						return is_user_logged_in();
					},
				],
				'args' => [
					'data' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => [ $this, 'sanitizeData' ],
					],
					'id'   => [
						'type'     => 'int',
						'required' => true,
					],
				],
			]
		);
	}

	/**
	 * Sanitize profile data object
	 *
	 * @param string $value JSON encoded string representing profile data
	 * @param \WP_REST_Request $request
	 * @param string $param
	 *
	 * @return object
	 *
	 * @since 2.11.0
	 */
	public function sanitizeData( $value, $request, $param ) {

		$sanitizeHelper = '\Give\DonorProfiles\Helpers\SanitizeProfileData';
		$valuesArr      = json_decode( $value );

		return [
			'firstName'           => sanitize_text_field( $valuesArr['firstName'] ),
			'lastName'            => sanitize_text_field( $valuesArr['lastName'] ),
			'additionalEmails'    => SanitizeHelper::sanitizeAdditionalEmails( $valuesArr['additionalEmails'] ),
			'additionalAddresses' => SanitizeHelper::sanitizeAdditionalAddresses( $valuesArr['additionalAddresses'] ),
			'primaryEmail'        => sanitize_email( $valuesArr['primaryEmail'] ),
			'primaryAddress'      => SanitizeHelper::sanitizeAddress( $valuesArr['primaryAddress'] ),
			'titlePrefix'         => sanitize_text_field( $valuesArr['titlePrefix'] ),
			'avatarId'            => SanitizeHelper::sanitizeInt( $valuesArr['avatarId'] ),
		];

	}

	/**
	 * Handles profile update, and returns updated profile array
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.11.0
	 */
	public function handleRequest( WP_REST_Request $request ) {
		return $this->updateProfile( $request->get_param( 'data' ), $request->get_param( 'id' ) );
	}

	/**
	 * Updates profile model, then gets newly stored profile data to return
	 *
	 * @param object $data Object representing profile data to update
	 * @param int $id Profile id to update
	 *
	 * @return array
	 *
	 * @since 2.11.0
	 */
	protected function updateProfile( $data, $id ) {
		$profile = new Profile( $id );
		$profile->update( $data );
		return [
			'profile' => $profile->getProfileData(),
		];
	}

	protected function sanitizeValue( $value, $config ) {
		if ( ! empty( $value ) && is_callable( $config['sanitizeCallback'] ) ) {
			return $config['sanitizeCallback']( $value );
		} else {
			return $config['default'];
		}
	}
}
