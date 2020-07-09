<?php

/**
 * Setup Page class
 *
 * @package Give
 */

namespace Give\Views\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Manages setup admin page
 */
class Setup {

	/**
	 * Initialize Reports Admin page
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'add_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function __construct() {
		// Do nothing
	}

	// Add Setup submenu page to admin menu
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

	public function enqueue_scripts() {
		wp_enqueue_style(
			'give-admin-setup-style',
			GIVE_PLUGIN_URL . 'assets/dist/css/admin-setup.css',
			[],
			'0.0.1'
		);
	}

	public function render_page() {
		include GIVE_PLUGIN_DIR . 'src/Views/Admin/Pages/templates/setup-template.php';
	}

	public function render_template( $template, $data = [] ) {
		extract( $data );
		include GIVE_PLUGIN_DIR . "src/Views/Admin/Pages/templates/$template.php";
	}
}
