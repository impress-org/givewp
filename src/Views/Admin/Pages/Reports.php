<?php

/**
 * Reports Page class
 *
 * @package Give
 */

namespace Give\Views\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Manages reports admin page
 */
class Reports {
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

	// Enqueue app scripts
	public function enqueue_scripts( $base ) {

		if ( $base !== 'give_forms_page_give-reports' ) {
			return;
		}

		if ( isset( $_GET['legacy'] ) ) {
			$script = "
				jQuery(document).ready(() => {
					const anchors = [].slice.call(document.querySelectorAll('a[href*=give-reports]'));
					anchors.forEach((anchor) => {
						if (anchor.getAttribute('id') === 'new-reports-link') {
							return;
						}
						anchor.setAttribute('href', anchor.getAttribute('href') + '&legacy=true');
					});
				});
			";
			wp_add_inline_script( 'jquery', $script );
			return;
		}

		wp_enqueue_style(
			'give-admin-reports-v3-style',
			GIVE_PLUGIN_URL . 'assets/dist/css/admin-reports.css',
			[],
			'0.0.1'
		);
		wp_enqueue_script(
			'give-admin-reports-v3-js',
			GIVE_PLUGIN_URL . 'assets/dist/js/admin-reports.js',
			[ 'wp-element', 'wp-api', 'wp-i18n' ],
			'0.0.1',
			true
		);
		wp_set_script_translations( 'give-admin-reports-v3-js', 'give' );
		wp_localize_script(
			'give-admin-reports-v3-js',
			'giveReportsData',
			[
				'legacyReportsUrl' => admin_url( '/edit.php?post_type=give_forms&page=give-reports&legacy=true' ),
				'allTimeStart'     => $this->get_all_time_start(),
			]
		);

	}

	// Add Reports submenu page to admin menu
	public function add_page() {
		$render = [ $this, 'render_template' ];
		if ( isset( $_GET['legacy'] ) ) {
			$render = [ Give()->give_settings, 'output' ];
		}

		add_submenu_page(
			'edit.php?post_type=give_forms',
			esc_html__( 'Donation Reports', 'give' ),
			esc_html__( 'Reports', 'give' ),
			'view_give_reports',
			'give-reports',
			$render
		);
	}

	public function render_template() {
		include_once GIVE_PLUGIN_DIR . 'src/Views/Admin/Pages/templates/reports-template.php';
	}

	public function get_all_time_start() {

		$start = date_create( '01/01/2015' );
		$end   = date_create();

		// Setup donation query args (get sanitized start/end date from request)
		$args = [
			'number'     => 1,
			'paged'      => 1,
			'orderby'    => 'date',
			'order'      => 'ASC',
			'start_date' => $start->format( 'Y-m-d H:i:s' ),
			'end_date'   => $end->format( 'Y-m-d H:i:s' ),
		];

		// Get array of 50 recent donations
		$donations = new \Give_Payments_Query( $args );
		$donations = $donations->get_payments();

		$earliest = $donations[0]->date;

		return $earliest;
	}
}
