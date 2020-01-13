<?php

/**
 * Reports Dashboard Widgets class
 *
 * @package Give
 */

namespace Give\Views\Admin\DashboardWidgets;

defined('ABSPATH') || exit;

/**
 * Manages reports dashboard widget view
 */
class Reports {

	/**
	 * Initialize Reports Dashboard Widget
	 */
	public function init() {
		add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
	}

	public function __construct()
	{
		//Do nothing
	}

	//Add dashboard widget
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'givewp_reports_widget',
			'GiveWP',
			[$this, 'render_template']
		);
	}

	//Enqueue app scripts
	public function enqueue_scripts($base) {
		if ($base !== 'index.php') {
			return;
		}

		wp_enqueue_style(
			'give-admin-reports-widget-style',
			GIVE_PLUGIN_URL . 'assets/dist/css/admin-reports-widget.css',
			[],
			'0.0.1'
		);
		wp_enqueue_script(
			'give-admin-reports-widget-js',
			GIVE_PLUGIN_URL . 'assets/dist/js/admin-reports-widget.js',
			['wp-element', 'wp-api', 'wp-i18n'],
			'0.0.1',
			true
		);

	}

	public function render_template() {
		include_once GIVE_PLUGIN_DIR . 'src/Views/Admin/DashboardWidgets/templates/reports-template.php';
	}
}
