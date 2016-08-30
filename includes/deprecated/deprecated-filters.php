<?php
/**
 * Handle renamed filters
 */
global $give_map_deprecated_filters;

$give_map_deprecated_filters = array(
	// New filter hook                                 Old filter hook.
	'give_donation_data_before_gateway'          => 'give_purchase_data_before_gateway',
	'give_donation_form_required_fields'         => 'give_purchase_form_required_fields',
	'give_donation_stats_by_user'                => 'give_purchase_stats_by_user',
	'give_donation_from_name'                    => 'give_purchase_from_name',
	'give_donation_from_address'                 => 'give_purchase_from_address',
	'give_donation_labels'                       => 'give_payment_labels',
	'give_donations_table_views'                 => 'give_payments_table_views',
	'give_donations_table_columns'               => 'give_payments_table_columns',
	'give_donations_table_sortable_columns'      => 'give_payments_table_sortable_columns',
	'give_donations_table_column'                => 'give_payments_table_column',
	'give_donation_row_actions'                  => 'give_payment_row_actions',
	'give_donations_table_bulk_actions'          => 'give_payments_table_bulk_actions',
	'give_donation_gateways'                     => 'give_payment_gateways',
	'give_donation_gateways_order'               => 'give_payment_gateways_order',
	'give_donation_meta'                         => 'give_payment_meta',
	'give_donation_add_donation_args'            => 'give_payment_add_donation_args',
	'give_donation_fee_keys'                     => 'give_payment_fee_keys',
	'give_donation_currency_default'             => 'give_payment_currency_default',
	'give_donation_completed_date'               => 'give_payment_completed_date',
	'give_donation_currency_code'                => 'give_payment_currency_code',
	'give_donation_gateway'                      => 'give_payment_gateway',
	'give_donation_user_ip'                      => 'give_payment_user_ip',
	'give_donation_customer_id'                  => 'give_payment_customer_id',
	'give_donation_user_id'                      => 'give_payment_user_id',
	'give_donation_user_email'                   => 'give_payment_user_email',
	'give_donation_meta_user_info'               => 'give_payment_meta_user_info',
	'give_donation_address'                      => 'give_payment_address',
	'give_donation_key'                          => 'give_payment_key',
	'give_donation_form_id'                      => 'give_payment_form_id',
	'give_donation_number'                       => 'give_payment_number',
	'give_donation'                              => 'give_payment',
	'give_donation_statuses'                     => 'give_payment_statuses',
	'give_donation_currency'                     => 'give_payment_currency',
	'give_donation_amount'                       => 'give_payment_amount',
	'give_get_users_donations_args'              => 'give_get_users_purchases_args',
	'give_recount_donors_donation_statuses'      => 'give_recount_customer_payment_statuses',
	'give_donor_recount_should_process_donation' => 'give_customer_recount_should_process_payment',
	'give_is_guest_donation'                     => 'give_is_guest_payment',
);


// Dynamic filters.
switch ( true ) {
	case ( ! empty( $_GET['payment-confirmation'] ) ) :
		$give_map_deprecated_filters["give_donation_confirm_{$_GET['payment-confirmation']}"] = "give_payment_confirm_{$_GET['payment-confirmation']}";
}

foreach ( $give_map_deprecated_filters as $new => $old ) {
	add_filter( $new, 'give_deprecated_filter_mapping', 10, 4 );
}

/**
 * Deprecated filter mapping.
 *
 * @param        $data
 * @param string $arg_1
 * @param string $arg_2
 * @param string $arg_3
 *
 * @return mixed|void
 */
function give_deprecated_filter_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
	global $give_map_deprecated_filters;

	$filter = current_filter();

	if ( isset( $give_map_deprecated_filters[ $filter ] ) ) {
		if ( has_filter( $give_map_deprecated_filters[ $filter ] ) ) {
			$data = apply_filters( $give_map_deprecated_filters[ $filter ], $data, $arg_1, $arg_2, $arg_3 );

			if ( ! defined( 'DOING_AJAX' ) ) {
				_give_deprecated_function( 'The ' . $give_map_deprecated_filters[ $filter ] . ' filter', '1.7', $filter );
			}
		}
	}

	return $data;
}