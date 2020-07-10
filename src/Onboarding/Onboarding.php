<?php

/**
 * Onboarding class
 *
 * @package Give
 */

namespace Give\Onboarding;

defined( 'ABSPATH' ) || exit;

/**
 *
 */
class Onboarding {

	/**
	 * Initialize Reports and Pages, register hooks
	 */
	public function init() {
		add_action( 'admin_post_dismiss_setup_page', [ $this, 'dismissSetupPage' ] );
	}

	public function dismissSetupPage() {
		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'dismiss_setup_page' ) ) {
			$foo = give_update_option( 'setup_page_enabled', false );

			wp_redirect( add_query_arg( [ 'post_type' => 'give_forms' ], admin_url( 'edit.php' ) ) );
			exit;
		}
	}

	public static function isSetupPageEnabled() {
		return give_get_option( 'setup_page_enabled', false );
	}
}

$onboarding = new Onboarding();
$onboarding->init();
