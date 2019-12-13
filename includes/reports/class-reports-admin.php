<?php
/**
 * Reports Admin class
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

/**
 * Manages reports admin registration
 */
class Reports_Admin {

	/**
	 * Initialize Reports Admin pages
	 */
	public function init() {
		add_action( 'admin_menu', [$this, 'register_submenu_page'] );
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
	}

	public function __construct() {
        //Do nothing
	}

	//Enqueue app scripts
	public function enqueue_scripts($base) {
		if ($base === 'give_forms_page_give-reports-v3' ) {
			wp_enqueue_script(
				'give-admin-reports-v3-js',
				GIVE_PLUGIN_URL . 'assets/dist/js/admin-reports.js',
				['wp-element', 'wp-api'],
				'0.0.1',
				true
			);
			wp_localize_script('give-admin-reports-v3-js', 'giveReportsData', [
				'app' => self::get_app_object(),
			]);
		}
	}

	//Return array of app data, to be accessed by frontend scripts
	public function get_app_object() {
		$object = [
			'pages' => []
		];

		return $object;
	}

	//Add Reports submenu page to admin menu
	public function register_submenu_page() {
		add_submenu_page(
			'edit.php?post_type=give_forms',
			esc_html__( 'Donation Reports', 'give' ),
			esc_html__( 'Reports v3', 'give' ),
			'view_give_reports',
			'give-reports-v3',
			[$this, 'render_template']
		);
	}

	public function render_template() {
		include_once GIVE_PLUGIN_DIR . 'includes/reports/template.php';
    }
    
}