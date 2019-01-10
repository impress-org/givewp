<?php
/**
 * Dashboard Widgets
 *
 * @package     Give
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the dashboard widgets
 *
 * @since  1.0
 * @return void
 */
function give_register_dashboard_widgets() {
	if ( current_user_can( apply_filters( 'give_dashboard_stats_cap', 'view_give_reports' ) ) ) {
		wp_add_dashboard_widget( 'give_dashboard_sales', __( 'Give: Donation Statistics', 'give' ), 'give_render_dashboard_stats_widget' );
	}
}

add_action( 'wp_dashboard_setup', 'give_register_dashboard_widgets', 10 );


/**
 * Sales Summary Dashboard Widget render callback
 * Note: only for internal use
 *
 * Builds and renders the ajaxify statistics dashboard widget.
 * This widget displays the current month's donations.
 *
 * @since  2.4.0
 * @return void
 */
function give_render_dashboard_stats_widget() {
	if ( ! current_user_can( apply_filters( 'give_dashboard_stats_cap', 'view_give_reports' ) ) ) {
		return;
	}

	?>
	<div id="give-dashboard-sales-widget">
		<span class="spinner is-active" style="float: none;margin: auto 50%;padding-bottom: 15px;"></span>

		<script>
			jQuery(document).ready(function () {
				jQuery.ajax({
					url: ajaxurl,
					data: {
						action: 'give_render_dashboard_stats_widget'
					},
					success: function (response) {
						jQuery('#give-dashboard-sales-widget').html(response);
					}
				});
			})
		</script>
	</div>
	<?php
}

/**
 * Ajax handler for dashboard statistic widget render
 * Note: only for internal use
 *
 * @since 2.4.0
 */
function give_ajax_render_dashboard_stats_widget(){
	ob_start();
	give_dashboard_stats_widget();

	wp_send_json( ob_get_clean() );

}
add_action( 'wp_ajax_give_render_dashboard_stats_widget', 'give_ajax_render_dashboard_stats_widget' );

/**
 * Sales Summary Dashboard Widget
 *
 * Builds and renders the statistics dashboard widget. This widget displays the current month's donations.
 *
 * @since       1.0
 * @return void
 */
function give_dashboard_stats_widget() {

	if ( ! current_user_can( apply_filters( 'give_dashboard_stats_cap', 'view_give_reports' ) ) ) {
		return;
	}
	$stats = new Give_Payment_Stats(); ?>

	<div class="give-dashboard-widget">

		<div class="give-dashboard-today give-clearfix">
			<h3 class="give-dashboard-date-today"><?php echo date_i18n( _x( 'F j, Y', 'dashboard widget', 'give' ) ); ?></h3>

			<p class="give-dashboard-happy-day"><?php
				printf(
				/* translators: %s: day of the week */
					__( 'Happy %s!', 'give' ),
					date_i18n( 'l', current_time( 'timestamp' ) )
				);
			?></p>

			<p class="give-dashboard-today-earnings"><?php
				$earnings_today = $stats->get_earnings( 0, 'today', false );
				echo give_currency_filter( give_format_amount( $earnings_today, array( 'sanitize' => false ) ) );
			?></p>

			<p class="give-donations-today"><?php
				$donations_today = $stats->get_sales( 0, 'today', false );
				printf(
					/* translators: %s: daily donation count */
					__( '%s donations today', 'give' ),
					give_format_amount( $donations_today, array( 'decimal' => false, 'sanitize' => false ) )
				);
			?></p>

		</div>


		<table class="give-table-stats">
			<thead style="display: none;">
			<tr>
				<th><?php _e( 'This Week', 'give' ); ?></th>
				<th><?php _e( 'This Month', 'give' ); ?></th>
				<th><?php _e( 'Past 30 Days', 'give' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<tr id="give-table-stats-tr-1">
				<td>
					<p class="give-dashboard-stat-total"><?php echo give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'this_week' ), array( 'sanitize' => false ) ) ); ?></p>

					<p class="give-dashboard-stat-total-label"><?php _e( 'This Week', 'give' ); ?></p>
				</td>
				<td>
					<p class="give-dashboard-stat-total"><?php echo give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'this_month' ), array( 'sanitize' => false ) ) ); ?></p>

					<p class="give-dashboard-stat-total-label"><?php _e( 'This Month', 'give' ); ?></p>
				</td>
			</tr>
			<tr id="give-table-stats-tr-2">
				<td>
					<p class="give-dashboard-stat-total"><?php echo give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'last_month' ), array( 'sanitize' => false ) ) ) ?></p>

					<p class="give-dashboard-stat-total-label"><?php _e( 'Last Month', 'give' ); ?></p>
				</td>
				<td>
					<p class="give-dashboard-stat-total"><?php echo give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'this_quarter' ), array( 'sanitize' => false ) ) ) ?></p>

					<p class="give-dashboard-stat-total-label"><?php _e( 'This Quarter', 'give' ); ?></p>
				</td>
			</tr>
			</tbody>
		</table>

	</div>

	<?php
}

/**
 * Add donation forms count to dashboard "At a glance" widget
 *
 * @since  1.0
 *
 * @param $items
 *
 * @return array
 */
function give_dashboard_at_a_glance_widget( $items ) {

	$num_posts = wp_count_posts( 'give_forms' );

	if ( $num_posts && $num_posts->publish ) {

		$text = sprintf(
			/* translators: %s: number of posts published */
			_n( '%s Give Form', '%s Give Forms', $num_posts->publish, 'give' ),
			$num_posts->publish
		);

		$text = sprintf( $text, number_format_i18n( $num_posts->publish ) );

		if ( current_user_can( 'edit_give_forms', get_current_user_id() ) ) {
			$text = sprintf(
				'<a class="give-forms-count" href="%1$s">%2$s</a>',
				admin_url( 'edit.php?post_type=give_forms' ),
				$text
			);
		} else {
			$text = sprintf(
				'<span class="give-forms-count">%1$s</span>',
				$text
			);
		}

		$items[] = $text;
	}

	return $items;
}

add_filter( 'dashboard_glance_items', 'give_dashboard_at_a_glance_widget', 1, 1 );
