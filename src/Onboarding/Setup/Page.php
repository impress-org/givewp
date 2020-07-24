<?php

/**
 * Onboarding class
 *
 * @package Give
 */

namespace Give\Onboarding\Setup;

defined( 'ABSPATH' ) || exit;

/**
 * Organizes WordPress actions and helper methods for Onboarding.
 *
 * @since 2.8.0
 */
class Page {

	use PageDismissible;

	public function hide_admin_notices() {
		if ( isset( $_GET['page'] ) && 'give-setup' == $_GET['page'] ) {
			ob_start();
			add_action( 'admin_notices', [ $this, '_hide_admin_notices' ], 999999 );
		}
	}

	public function _hide_admin_notices() {
		ob_get_clean();
	}

	/**
	 * Redirect the top level "Donations" menu to the Setup submenu.
	 *
	 * @note This adjusts the URL pattern so that the submenu page displays correctly.
	 *
	 * @since 2.8.0
	 */
	public function redirectDonationsToSetup() {
		if ( isset( $_GET['page'] ) && 'give-setup' == $_GET['page'] ) {
			if ( ! isset( $_GET['post_type'] ) ) {
				wp_redirect(
					add_query_arg(
						[
							'post_type' => 'give_forms',
							'page'      => 'give-setup',
						],
						admin_url( 'edit.php' )
					)
				);
				exit;
			}
		}
	}

	/**
	 * Add Setup submenu page to admin menu
	 *
	 * @since 2.8.0
	 */
	public function add_page() {
		add_submenu_page(
			'edit.php?post_type=give_forms',
			esc_html__( 'Setup GiveWP', 'give' ),
			esc_html__( 'Setup', 'give' ),
			'manage_give_settings',
			'give-setup',
			[ $this, 'render_page' ],
			$position = 0
		);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.8.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'give-admin-setup-style',
			GIVE_PLUGIN_URL . 'assets/dist/css/admin-setup.css',
			[],
			GIVE_VERSION
		);
		wp_enqueue_style(
			'give-admin-setup-google-fonts',
			'https://fonts.googleapis.com/css2?family=Open+Sans:wght@600&display=swap',
			[],
			GIVE_VERSION
		);
		wp_enqueue_script(
			'give-admin-setup-script',
			GIVE_PLUGIN_URL . 'assets/src/js/admin/admin-setup.js',
			[],
			'0.0.1',
			$in_footer = true
		);
		wp_enqueue_style(
			'give-admin-setup-google-fonts',
			'https://fonts.googleapis.com/css2?family=Open+Sans:wght@600&display=swap',
			[],
			GIVE_VERSION
		);
		wp_enqueue_script(
			'give-admin-setup-script',
			GIVE_PLUGIN_URL . 'assets/src/js/admin/admin-setup.js',
			[],
			'0.0.1',
			$in_footer = true
		);
	}

	/**
	 * Render the submenu page
	 *
	 * @since 2.8.0
	 */
	public function render_page() {
		$view = new PageView();
		echo $view->render();
	}
}
