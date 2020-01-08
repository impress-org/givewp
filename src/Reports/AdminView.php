<?php

/**
 * Reports Admin class
 *
 * @package Give
 */

namespace Give\Reports;

defined('ABSPATH') || exit;

/**
 * Manages reports admin registration
 */
class AdminView
{

	/**
	 * Initialize Reports Admin pages
	 */
	public function init()
	{
		add_action('admin_menu', [$this, 'register_submenu_page']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
	}

	public function __construct()
	{
		//Do nothing
	}

	//Add dashboard widget
	public function add_dashboard_widget()
	{
		wp_add_dashboard_widget(
			'givewp_reports_widget',
			'GiveWP',
			[$this, 'render_widget_template']
		);
	}

	//Enqueue app scripts
	public function enqueue_scripts($base)
	{
		if ($base === 'give_forms_page_give-reports-v3') {
			wp_enqueue_style(
				'give-admin-reports-v3-style',
				GIVE_PLUGIN_URL . 'assets/dist/css/admin-reports.css',
				[],
				'0.0.1'
			);
			wp_enqueue_script(
				'give-admin-reports-v3-js',
				GIVE_PLUGIN_URL . 'assets/dist/js/admin-reports.js',
				['wp-element', 'wp-api', 'wp-i18n'],
				'0.0.1',
				true
			);
			wp_set_script_translations('give-admin-reports-v3-js', 'give');
			wp_localize_script('give-admin-reports-v3-js', 'giveReportsData', [
				'legacyReportsUrl' => admin_url('/edit.php?post_type=give_forms&page=give-reports')
			]);
		} else if ($base === 'index.php') {
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
	}

	//Add Reports submenu page to admin menu
	public function register_submenu_page()
	{
		add_submenu_page(
			'edit.php?post_type=give_forms',
			esc_html__('Donation Reports', 'give'),
			esc_html__('Reports', 'give'),
			'view_give_reports',
			'give-reports-v3',
			[$this, 'render_template']
		);
	}

	public function render_template()
	{
		include_once GIVE_PLUGIN_DIR . 'src/Reports/Views/template.php';
	}

	public function render_widget_template()
	{
		include_once GIVE_PLUGIN_DIR . 'src/Reports/Views/widget-template.php';
	}
}
