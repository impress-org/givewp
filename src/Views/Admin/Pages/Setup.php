<?php

/**
 * Setup Page class
 *
 * @package Give
 */

namespace Give\Views\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Organizes WordPress actions for the Setup Page submenu.
 *
 * @since 2.8.0
 */
class Setup {

	/**
	 * Initialize Reports Admin page
	 *
	 * @since 2.8.0
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'redirectDonationsToSetup' ] );
		add_action( 'admin_menu', [ $this, 'add_page' ] );
		add_action( 'admin_notices', [ $this, 'hide_admin_notices' ], -999999 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function __construct() {
		// Do nothing
	}

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
			'0.0.1'
		);
	}

	/**
	 * Render the submenu page
	 *
	 * @since 2.8.0
	 */
	public function render_page() {
		include GIVE_PLUGIN_DIR . 'src/Views/Admin/Pages/templates/setup-page/index.html.php';
	}

	/**
	 * Render templates
	 *
	 * @param string $template
	 * @param array $data The key/value pairs passed as $data are extracted as variables for use within the template file.
	 *
	 * @since 2.8.0
	 */
	public function render_template( $template, $data = [] ) {
		ob_start();
		include GIVE_PLUGIN_DIR . "src/Views/Admin/Pages/templates/$template.html";
		$output = ob_get_clean();

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( '', $value );
			}
			$output = preg_replace( '/{{\s*' . $key . '\s*}}/', $value, $output );
		}

		// Stripe unmerged tags.
		$output = preg_replace( '/{{\s*.*\s*}}/', '', $output );

		return $output;
	}

	/**
	 * Returns a qualified image URL.
	 *
	 * @param string $src
	 *
	 * @return string
	 */
	public function image( $src ) {
		return GIVE_PLUGIN_URL . "assets/dist/images/setup-page/$src";
	}
}
