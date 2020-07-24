<?php

/**
 * Onboarding class
 *
 * @package Give
 */

namespace Give\Onboarding\Setup;

use Give\Onboarding\Setup\PageStatus;

defined( 'ABSPATH' ) || exit;

trait PageDismissible {

	/**
	 * Dissmiss the Setup Page.
	 *
	 * @since 2.8.0
	 */
	public function dismissSetupPage() {
		if ( wp_verify_nonce( $_GET['_wpnonce'], 'dismiss_setup_page' ) ) {
			give_update_option( 'setup_page_enabled', PageStatus::DISABLED );

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
	public function getSetupPageEnabledOrDisabled() {
		return give_get_option( 'setup_page_enabled', PageStatus::DISABLED );
	}

}
