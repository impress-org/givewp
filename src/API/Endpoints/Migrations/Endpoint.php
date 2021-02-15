<?php

namespace Give\API\Endpoints\Migrations;

use Give\API\RestRoute;
use WP_Error;

/**
 * Class Endpoint
 * @package Give\API\Endpoints\Migrations
 *
 * @since 2.10.0
 */
abstract class Endpoint implements RestRoute {

	/**
	 * @var string
	 */
	protected $endpoint;

	/**
	 * Check user permissions
	 * @return bool|WP_Error
	 */
	public function permissionsCheck() {
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'You dont have the right permissions to view Migrations', 'give' ),
				[ 'status' => $this->authorizationStatusCode() ]
			);
		}

		return true;
	}

	/**
	 * Check if database updates are enabled
	 * @return bool|WP_Error
	 */
	public function runPermissionsCheck() {
		$permissionsCheck = $this->permissionsCheck();

		if ( is_wp_error( $permissionsCheck ) ) {
			return $permissionsCheck;
		}

		if ( 'enabled' !== give_get_option( 'enable_database_updates' ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'Database updates are disabled', 'give' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	// Sets up the proper HTTP status code for authorization.
	public function authorizationStatusCode() {
		if ( is_user_logged_in() ) {
			return 403;
		}

		return 401;
	}
}
