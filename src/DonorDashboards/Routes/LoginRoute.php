<?php

namespace Give\DonorDashboards\Routes;

use WP_REST_Request;
use WP_REST_Response;
use Give\API\RestRoute;

/**
 * @since 2.10.0
 */
class LoginRoute implements RestRoute {

	/** @var string */
	protected $endpoint = 'donor-dashboard/login';

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
					'permission_callback' => '__return_true',
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
	 * @since 2.10.0
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
							'message' => __( 'The provided password was incorrect.', 'give' ),
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
						'message' => sprintf( __( 'A record for the provided login (%s) could not be found.', 'give' ), $login ),
					],
				]
			);
		}
	}
}
