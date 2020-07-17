<?php

/**
 * Onboarding class
 *
 * @package Give
 */

namespace Give\Onboarding;

defined( 'ABSPATH' ) || exit;

/**
 * Organizes WordPress actions and helper methods for Onboarding.
 *
 * @since 2.8.0
 */
class Onboarding {

	const ENABLED  = 'enabled';
	const DISABLED = 'disabled';

	/**
	 * Initialize the Onboarding hooks/actions.
	 *
	 * @since 2.8.0
	 */
	public function init() {
		add_action( 'admin_post_dismiss_setup_page', [ $this, 'dismissSetupPage' ] );
	}

	/**
	 * Dissmiss the Setup Page.
	 *
	 * @since 2.8.0
	 */
	public function dismissSetupPage() {
		if ( wp_verify_nonce( $_GET['_wpnonce'], 'dismiss_setup_page' ) ) {
			give_update_option( 'setup_page_enabled', self::DISABLED );

			wp_redirect( add_query_arg( [ 'post_type' => 'give_forms' ], admin_url( 'edit.php' ) ) );
			exit;
		}
	}

	/**
	 * Helper method for checking the if the Setup Page is enabled.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public static function getSetupPageEnabledOrDisabled() {
		return give_get_option( 'setup_page_enabled', self::DISABLED );
	}
}

$onboarding = new Onboarding();
$onboarding->init();
