<?php
/**
 * Handle renamed filters
 *
 * @package Give
 */

$give_map_deprecated_filters = give_deprecated_filters();

foreach ( $give_map_deprecated_filters as $new => $old ) {
	add_filter( $new, 'give_deprecated_filter_mapping', 10, 4 );
}

/**
 * Deprecated filters.
 *
 * @return array An array of deprecated Give filters.
 */
function give_deprecated_filters() {

	$give_deprecated_filters = array(
		// New filter hook                                 Old filter hook.
		'give_donation_data_before_gateway'                => 'give_purchase_data_before_gateway',
		'give_donation_form_required_fields'               => 'give_purchase_form_required_fields',
		'give_donation_stats_by_user'                      => 'give_purchase_stats_by_user',
		'give_donation_from_name'                          => 'give_purchase_from_name',
		'give_donation_from_address'                       => 'give_purchase_from_address',
		'give_get_users_donations_args'                    => 'give_get_users_purchases_args',
		'give_recount_donors_donation_statuses'            => 'give_recount_customer_payment_statuses',
		'give_donor_recount_should_process_donation'       => 'give_customer_recount_should_process_payment',
		'give_reset_items'                                 => 'give_reset_store_items',
		'give_decrease_donations_on_undo'                  => 'give_decrease_sales_on_undo',
		'give_decrease_earnings_on_pending'                => 'give_decrease_store_earnings_on_pending',
		'give_decrease_donor_value_on_pending'             => 'give_decrease_customer_value_on_pending',
		'give_decrease_donors_donation_count_on_pending'   => 'give_decrease_customer_purchase_count_on_pending',
		'give_decrease_earnings_on_cancelled'              => 'give_decrease_store_earnings_on_cancelled',
		'give_decrease_donor_value_on_cancelled'           => 'give_decrease_customer_value_on_cancelled',
		'give_decrease_donors_donation_count_on_cancelled' => 'give_decrease_customer_purchase_count_on_cancelled',
		'give_decrease_earnings_on_revoked'                => 'give_decrease_store_earnings_on_revoked',
		'give_decrease_donor_value_on_revoked'             => 'give_decrease_customer_value_on_revoked',
		'give_decrease_donors_donation_count_on_revoked'   => 'give_decrease_customer_purchase_count_on_revoked',
		'give_edit_donors_role'                            => 'give_edit_customers_role',
		'give_edit_donor_info'                             => 'give_edit_customer_info',
		'give_edit_donor_address'                          => 'give_edit_customer_address',
		'give_donor_tabs'                                  => 'give_customer_tabs',
		'give_donor_views'                                 => 'give_customer_views',
		'give_view_donors_role'                            => 'give_view_customers_role',
		'give_report_donor_columns'                        => 'give_report_customer_columns',
		'give_report_sortable_donor_columns'               => 'give_report_sortable_customer_columns',
		'give_undo_donation_statuses'                      => 'give_undo_purchase_statuses',
		'give_donor_recount_should_increase_value'         => 'give_customer_recount_sholud_increase_value',
		'give_donor_recount_should_increase_count'         => 'give_customer_recount_should_increase_count',
		'give_donation_amount'                             => 'give_payment_amount',
		'give_get_donation_form_title'                     => 'give_get_payment_form_title',
		'give_decrease_earnings_on_refunded'               => 'give_decrease_store_earnings_on_refund',
		'give_decrease_donor_value_on_refunded'            => 'give_decrease_customer_value_on_refund',
		'give_decrease_donors_donation_count_on_refunded'  => 'give_decrease_customer_purchase_count_on_refund',
		'give_should_process_refunded'                     => 'give_should_process_refund',
		'give_settings_export_excludes'                    => 'settings_export_excludes',
		'give_ajax_form_search_response'                   => 'give_ajax_form_search_responce',
	);

	return $give_deprecated_filters;
}

/**
 * Deprecated filter mapping.
 *
 * @param mixed  $data
 * @param string $arg_1 Passed filter argument 1.
 * @param string $arg_2 Passed filter argument 2.
 * @param string $arg_3 Passed filter argument 3.
 *
 * @return mixed
 */
function give_deprecated_filter_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
	$give_map_deprecated_filters = give_deprecated_filters();
	$filter                      = current_filter();

	if ( isset( $give_map_deprecated_filters[ $filter ] ) ) {
		if ( has_filter( $give_map_deprecated_filters[ $filter ] ) ) {
			$data = apply_filters( $give_map_deprecated_filters[ $filter ], $data, $arg_1, $arg_2, $arg_3 );

			if ( ! defined( 'DOING_AJAX' ) ) {
				_give_deprecated_function(
					sprintf( /* translators: %s: filter name */
						__( 'The %s filter' ),
						$give_map_deprecated_filters[ $filter ]
					),
					'1.7',
					$filter
				);
			}
		}
	}

	return $data;
}
