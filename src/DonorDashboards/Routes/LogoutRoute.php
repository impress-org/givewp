<?php

namespace Give\DonorDashboards\Routes;

use WP_REST_Request;
use WP_REST_Response;
use Give\API\RestRoute;
use WP_Error;

/**
 * @since 2.10.0
 */
class LogoutRoute implements RestRoute {

	/** @var string */
	protected $endpoint = 'donor-dashboard/logout';

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
					'permission_callback' => [ $this, 'permissionsCheck' ],
				],
			]
		);
	}

	/**
	 * Handles logout request
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Request
	 *
	 * @since 2.10.0
	 */
	public function handleRequest( WP_REST_Request $request ) {

		// Check if WP user is logged in
		if ( get_current_user_id() !== 0 ) {

			// Handle logout logic for WP users

			/**
			 * Fires before processing user logout.
			 *
			 * @since 1.0
			 */
			do_action( 'give_before_user_logout' );

			// Logout user (and destroys current Give Session, via hook registered in Give_Session class)
			wp_logout();

			/**
			 * Fires after processing user logout.
			 *
			 * @since 1.0
			 */
			do_action( 'give_after_user_logout' );

		} else {

			// Destroy current Give Session
			give()->session->destroy_session();

		}

		return new WP_REST_Response(
			[
				'status'        => 200,
				'response'      => 'logout_successful',
				'body_response' => [
					'message' => __( 'User was logged out successfully.', 'give' ),
				],
			]
		);
	}

	/**
	 * Check permissions
	 *
	 * @return bool
	 */
	public function permissionsCheck() {
		return Give()->session->get_session_expiration() !== false || is_user_logged_in();
	}
}
