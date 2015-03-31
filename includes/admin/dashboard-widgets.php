<?php
/**
 * Dashboard Widgets
 *
 * @package     Give
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
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
		wp_add_dashboard_widget( 'give_dashboard_sales', __( 'Give: Donation Statistics', 'give' ), 'give_dashboard_sales_widget' );
	}
}

add_action( 'wp_dashboard_setup', 'give_register_dashboard_widgets', 10 );

/**
 * Sales Summary Dashboard Widget
 *
 * @descriptions: Builds and renders the statistics dashboard widget. This widget displays the current month's donations.
 *
 * @since       1.0
 * @return void
 */
function give_dashboard_sales_widget() {

	if ( ! current_user_can( apply_filters( 'give_dashboard_stats_cap', 'view_give_reports' ) ) ) {
		return;
	}
	$stats = new Give_Payment_Stats; ?>

	<div class="give-dashboard-widget">

		<div class="give-dashboard-today give-clearfix">
			<h3 class="give-dashboard-date-today"><?php echo date( 'F j, Y' ); ?></h3>

			<p class="give-dashboard-happy-day"><?php printf( __( 'Happy %1$s!', 'give' ), date( 'l', current_time( 'timestamp' ) ) ); ?></p>

			<?php $earnings_today = $stats->get_earnings( 0, 'today', false ); ?>

			<p class="give-dashboard-today-earnings"><?php echo give_currency_filter( give_format_amount( $earnings_today ) ); ?></p>

			<p class="give-orders-today"><?php $donations_today = $stats->get_sales( 0, 'today', false, array(
					'publish',
					'revoked'
				) ); ?><?php echo give_format_amount( $donations_today, false ); ?>
				<span><?php echo _x( 'donations today', 'Displays in WP admin dashboard widget after the day\'s total donations', 'give' ); ?></span>
			</p>


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
					<p class="give-dashboard-stat-total"><?php echo give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'this_week' ) ) ); ?></p>

					<p class="give-dashboard-stat-total-label"><?php _e( 'this week', 'give' ); ?></p>
				</td>
				<td>
					<p class="give-dashboard-stat-total"><?php echo give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'this_month' ) ) ); ?></p>

					<p class="give-dashboard-stat-total-label"><?php _e( 'this month', 'give' ); ?></p>
				</td>
			</tr>
			<tr id="give-table-stats-tr-2">
				<td>
					<p class="give-dashboard-stat-total"><?php echo give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'last_month' ) ) ) ?></p>

					<p class="give-dashboard-stat-total-label"><?php _e( 'last month', 'give' ); ?></p>
				</td>
				<td>
					<p class="give-dashboard-stat-total"><?php echo give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'this_year', false ) ) ) ?></p>

					<p class="give-dashboard-stat-total-label"><?php _e( 'this year', 'give' ); ?></p>
				</td>
			</tr>
			</tbody>
		</table>

	</div>

<?php
}

/**
 * Add download count to At a glance widget
 *
 * @since  1.0
 * @return void
 */
function give_dashboard_at_a_glance_widget( $items ) {
	$num_posts = wp_count_posts( 'give_forms' );

	if ( $num_posts && $num_posts->publish ) {
		$text = _n( '%s Give ' . give_get_forms_label_singular(), '%s Give ' . give_get_forms_label_plural(), $num_posts->publish );

		$text = sprintf( $text, number_format_i18n( $num_posts->publish ) );

		if ( current_user_can( 'edit_give_forms', get_the_ID() ) ) {
			$text = sprintf( '<a class="give-forms-count" href="edit.php?post_type=give_forms">%1$s</a>', $text );
		} else {
			$text = sprintf( '<span class="give-forms-count">%1$s</span>', $text );
		}

		$items[] = $text;
	}

	return $items;
}

add_filter( 'dashboard_glance_items', 'give_dashboard_at_a_glance_widget', 1, 1);
