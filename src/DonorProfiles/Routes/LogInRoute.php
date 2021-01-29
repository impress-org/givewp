<?php

namespace Give\DonorProfiles\Routes;

use WP_REST_Request;
use WP_REST_Response;
use Give\API\RestRoute;
use Give\DonorProfiles\Profile as Profile;

/**
 * @since 2.11.0
 */
class LoginRoute implements RestRoute {

	/** @var string */
	protected $endpoint = 'donor-profile/login';

	/**
	 * @inheritDoc
	 */
	public function registerRoute() {
		register_rest_route(
			'give-api/v2',
			$this->endpoint,
			[
				[
					'methods'  => 'POST',
					'callback' => [ $this, 'handleRequest' ],
				],
				'args' => [
					'login'    => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'password' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);
	}

	/**
	 * Handles login request
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.11.0
	 */
	public function handleRequest( WP_REST_Request $request ) {

		$login    = $request->get_param( 'login' );
		$password = $request->get_param( 'password' );

		$user = get_user_by( 'login', $login );

		if ( ! $user ) {
			$user = get_user_by( 'email', $login );
		}

		if ( $user ) {
			if ( wp_check_password( $password, $user->user_pass, $user->ID ) ) {
				give_log_user_in( $user->ID, $login, $password );
				return new WP_REST_Response(
					[
						'status'        => 200,
						'response'      => 'login_successful',
						'body_response' => [
							'login' => $user->login,
							'id'    => $user->ID,
						],
					]
				);
			} else {
				return new WP_REST_Response(
					[
						'status'        => 400,
						'response'      => 'incorrect_password',
						'body_response' => [
							'message' => 'The provided password was incorrect.',
						],
					]
				);
			}
		} else {
			return new WP_REST_Response(
				[
					'status'        => 400,
					'response'      => 'unidentified_login',
					'body_response' => [
						'message' => "A record for the provided login ('{$login}') could not be found.",
					],
				]
			);
		}
	}
}
