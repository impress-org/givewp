<?php
/**
 * Handle renamed filters
 */
global $give_map_deprecated_filters;

$give_map_deprecated_filters = array(
	// New filter hook                       Old filter hook.
	'give_donation_data_before_gateway'  => 'give_purchase_data_before_gateway',
	'give_donation_form_required_fields' => 'give_purchase_form_required_fields',
	'give_donation_stats_by_user'        => 'give_purchase_stats_by_user',
	'give_donation_from_name'            => 'give_purchase_from_name',
	'give_donation_from_address'         => 'give_purchase_from_address',
);

foreach ( $give_map_deprecated_filters as $new => $old ){
	add_filter( $new, 'give_deprecated_filter_mapping', 10, 4 );
}

function give_deprecated_filter_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
	global $give_map_deprecated_filters;

	$filter = current_filter();

	if ( isset( $give_map_deprecated_filters[ $filter ] ) )
		if ( has_filter( $give_map_deprecated_filters[ $filter ] ) ) {
			$data = apply_filters( $give_map_deprecated_filters[ $filter ], $data, $arg_1, $arg_2, $arg_3 );
			_give_deprecated_function( 'The ' . $give_map_deprecated_filters[ $filter ] . ' filter', '1.7', $filter );
		}

	return $data;
}