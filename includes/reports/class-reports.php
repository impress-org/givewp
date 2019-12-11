<?php
/**
 * Reports manager
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

//Require reports
require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-donors-report.php';
require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-payments-report.php';
require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-campaigns-report.php';

//Require pages
require_once GIVE_PLUGIN_DIR . 'includes/reports/pages/class-overview-page.php';

/**
 * Manages the settings.
 */
class Reports {

	/**
	 * Information about all of the APIs.
	 * See `init` for structure of the data.
	 *
	 * @var array
	 */

	protected static $reports = [];
	protected static $pages = [];

	/**
	 * Initialize and register all of the post types
	 */
	public static function init() {
		static::$reports = [
            'payments' => new Payments_Report(),
			'donors' => new Donors_Report(),
			'campaigns' => new Campaigns_Report(),
		];
		static::$pages = [
			'overview' => new Overview_Page()
		];
		add_action( 'admin_menu', [__CLASS__, 'register_submenu_page'] );
		add_action( 'admin_enqueue_scripts', [__CLASS__, 'enqueue_scripts'] );
		add_action( 'rest_api_init', [__CLASS__, 'register_api_routes'] );
	}

	public static function enqueue_scripts() {
		wp_enqueue_script(
			'give-admin-reports-v3-js',
			GIVE_PLUGIN_URL . 'assets/dist/js/admin-reports.js',
			['wp-element', 'wp-api'],
			'0.0.1',
			true
		);
		wp_localize_script('give-admin-reports-v3-js', 'giveReportsData', [
			'app' => self::get_app_object()
		]);
	}

	public static function get_app_object() {
		$object = [
			'pages' => []
		];

		foreach (self::$pages as $slug => $class) {
			$object['pages'][$slug] = $class->get_page_object();
		}

		return $object;
	}

	public static function register_submenu_page() {
		add_submenu_page(
			'edit.php?post_type=give_forms',
			esc_html__( 'Donation Reports', 'give' ),
			esc_html__( 'Reports v3', 'give' ),
			'view_give_reports',
			'give-reports-v3',
			[__CLASS__, 'generate_output']
		);
	}

	public static function generate_output() {
		echo '<div id="reports-app"><h1>Loading</h1></div>';
	}

	/**
	 * Register all reports in API
	 */
	protected static function register_api_routes() {

		register_rest_route( 'givewp/v3', '/reports/report=(?P<report>[a-zA-Z0-9-]+)/lat=(?P<lat>[a-z0-9 .\-]+)/long=(?P<long>[a-z0-9 .\-]+)', array(
			'methods' => 'GET',
			'callback' => [__CLASS__, 'handle_report_callback'],
		));

		register_rest_route( 'givewp/v3', '/reports/page=(?P<page>[a-zA-Z0-9-]+)/lat=(?P<lat>[a-z0-9 .\-]+)/long=(?P<long>[a-z0-9 .\-]+)', array(
			'methods' => 'GET',
			'callback' => [__CLASS__, 'handle_page_callback'],
		));

	}

	protected static function handle_report_callback (WP_REST_Request $request) {
		$report = self::$reports[$request['report']];
		return $report->handle_api_callback($request);
	}

	protected static function handle_page_callback (WP_REST_Request $request) {
		$page = self::$pages[$request['page']];
		return $page->handle_api_callback($request);
	}

}
Reports::init();