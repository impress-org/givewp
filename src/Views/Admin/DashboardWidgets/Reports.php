<?php

/**
 * Reports Dashboard Widgets class
 *
 * @package Give
 */

namespace Give\Views\Admin\DashboardWidgets;

defined( 'ABSPATH' ) || exit;

/**
 * Manages reports dashboard widget view
 */
class Reports {

	/**
	 * Initialize Reports Dashboard Widget
	 */
	public function init() {
		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function __construct() {
		 // Do nothing
	}

	// Add dashboard widget
	public function add_dashboard_widget() {

		$reportsURL = admin_url( '/edit.php?post_type=give_forms&page=give-reports' );
		$reportsStr = __( 'GiveWP Donations: Reports', 'give' ) . '<a class="givewp-reports-link" href="' . $reportsURL . '">' . __( 'Visit Reports', 'give' ) . '</a>';

		if ( current_user_can( apply_filters( 'give_dashboard_stats_cap', 'view_give_reports' ) ) ) {
			wp_add_dashboard_widget(
				'givewp_reports_widget',
				$reportsStr,
				[ $this, 'render_template' ]
			);
		}
	}

	// Enqueue app scripts
	public function enqueue_scripts( $base ) {
		if ( $base !== 'index.php' ) {
			return;
		}

		wp_enqueue_style(
			'give-admin-reports-widget-style',
			GIVE_PLUGIN_URL . 'assets/dist/css/admin-reports-widget.css',
			[],
			GIVE_VERSION
		);
		wp_enqueue_script(
			'give-admin-reports-widget-js',
			GIVE_PLUGIN_URL . 'assets/dist/js/admin-reports-widget.js',
			[ 'wp-element', 'wp-api', 'wp-i18n' ],
			GIVE_VERSION,
			true
		);
		wp_localize_script(
			'give-admin-reports-widget-js',
			'giveReportsData',
			[
				'newFormUrl'   => admin_url( '/post-new.php?post_type=give_forms' ),
				'allTimeStart' => $this->get_all_time_start(),
				'currency'     => give_get_currency(),
				'testMode'     => give_is_test_mode(),
			]
		);

	}

	public function render_template() {
		include_once GIVE_PLUGIN_DIR . 'src/Views/Admin/DashboardWidgets/templates/reports-template.php';
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

		return isset( $donations[0] ) ? $donations[0]->date : $start->format( 'Y-m-d H:i:s' );
	}
}
