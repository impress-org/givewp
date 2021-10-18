<?php

/**
 * AdminViews Class
 *
 * @package Give
 */

namespace Give\Views;

use Give\Onboarding\Onboarding;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Admin Views
 */
class Admin {

	/**
	 * Initialize Admin views
	 */
	public function init() {

		// Load dashboard widgets
		$this->load_dashboard_widgets();

		// Load pages
		$this->load_pages();

	}

	public function __construct() {
		// Do nothing
	}

	public function load_dashboard_widgets() {
		// Load Reports dashboard widget
		$reports = new Admin\DashboardWidgets\Reports();
		$reports->init();
	}

	public function load_pages() {
		// Load Reports page
		$reports = new Admin\Pages\Reports();
		$reports->init();

		// ATTENTION: This is not for real, just for working on frontend
		add_action('admin_menu', function () {
			add_submenu_page(
				'edit.php?post_type=give_forms',
				esc_html__( 'Add-ons', 'give' ),
				esc_html__( 'Add-ons', 'give' ),
				'view_give_reports',
				'give-addons',
				function () {
					echo '<div id="root"></div>';
				}
			);
		});

		add_action('admin_enqueue_scripts', function ($base) {
			if ($base !== "give_forms_page_give-addons") return;

			wp_enqueue_style('give-admin-addon-gallery', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap');

			wp_enqueue_script(
				'give-admin-addon-gallery',
				GIVE_PLUGIN_URL . 'assets/dist/js/admin-addon-gallery.js',
				['wp-element', 'wp-i18n', 'wp-hooks'],
				'0.0.0',
				true
			);
		});
	}

}
