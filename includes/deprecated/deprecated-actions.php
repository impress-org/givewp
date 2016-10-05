<?php
/**
* Handle renamed actions.
*/
$give_map_deprecated_actions = give_deprecated_actions();

foreach ( $give_map_deprecated_actions as $new => $old ) {
	add_action( $new, 'give_deprecated_action_mapping', 10, 4 );
}

/**
 * Deprecated actions.
 *
 * @return array An array of deprecated Give actions.
 */
function give_deprecated_actions() {

	$give_deprecated_actions = array(
		// New action hook                            Old action hook.
		'give_donation_form_login_fields'          => 'give_purchase_form_login_fields',
		'give_donation_form_register_login_fields' => 'give_purchase_form_register_login_fields',
		'give_donation_form_before_register_login' => 'give_purchase_form_before_register_login',
		'give_donation_form_before_cc_form'        => 'give_purchase_form_before_cc_form',
		'give_donation_form_after_cc_form'         => 'give_purchase_form_after_cc_form',
		'give_donation_form_no_access'             => 'give_purchase_form_no_access',
		'give_donation_form_bottom'                => 'give_purchase_form_bottom',
		'give_donation_form_register_fields'       => 'give_purchase_form_register_fields',
		'give_donation_form_after_user_info'       => 'give_purchase_form_after_user_info',
		'give_donation_form_before_personal_info'  => 'give_purchase_form_before_personal_info',
		'give_donation_form_before_email'          => 'give_purchase_form_before_email',
		'give_donation_form_after_email'           => 'give_purchase_form_after_email',
		'give_donation_form_user_info'             => 'give_purchase_form_user_info',
		'give_donation_form_after_personal_info'   => 'give_purchase_form_after_personal_info',
		'give_donation_form'                       => 'give_purchase_form',
		'give_donation_form_wrap_bottom'           => 'give_purchase_form_wrap_bottom',
		'give_donation_form_before_submit'         => 'give_purchase_form_before_submit',
		'give_donation_form_after_submit'          => 'give_purchase_form_after_submit',
		'give_donation_history_header_before'      => 'give_purchase_history_header_before',
		'give_donation_history_header_after'       => 'give_purchase_history_header_after',
		'give_donation_history_row_start'          => 'give_purchase_history_row_start',
		'give_donation_history_row_end'            => 'give_purchase_history_row_end',
		'give_donation_form_top'                   => 'give_purchase_form_top',
		'give_donation_history_search'             => 'give_payment_history_search',
		'give_donations_table_do_bulk_action'      => 'give_payments_table_do_bulk_action',
		'give_donations_page_top'                  => 'give_payments_page_top',
		'give_donations_page_bottom'               => 'give_payments_page_bottom',
		'give_donation_personal_details_list'      => 'give_payment_personal_details_list',
		'give_donation_view_details'               => 'give_payment_view_details',
		'give_donation_billing_details'            => 'give_payment_billing_details',
		'give_donation_save'                       => 'give_payment_save',
		'give_donation_delete'                     => 'give_payment_delete',
		'give_donation_deleted'                    => 'give_payment_deleted',
		'give_donation_receipt_before_table'       => 'give_payment_receipt_before_table',
		'give_donation_receipt_header_before'      => 'give_payment_receipt_header_before',
		'give_donation_receipt_header_after'       => 'give_payment_receipt_header_after',
		'give_donation_receipt_before'             => 'give_payment_receipt_before',
		'give_donation_receipt_after'              => 'give_payment_receipt_after',
		'give_donation_receipt_after_table'        => 'give_payment_receipt_after_table',
		'give_donation_mode_select'                => 'give_payment_mode_select',
		'give_donation_mode_top'                   => 'give_payment_mode_top',
		'give_donation_mode_before_gateways_wrap'  => 'give_payment_mode_before_gateways_wrap',
		'give_donation_mode_before_gateways'       => 'give_payment_mode_before_gateways',
		'give_donation_mode_after_gateways'        => 'give_payment_mode_after_gateways',
		'give_donation_mode_after_gateways_wrap'   => 'give_payment_mode_after_gateways_wrap',
		'give_donation_mode_bottom'                => 'give_payment_mode_bottom',
	);

	return $give_deprecated_actions;
}

/**
 * Deprecated action mapping.
 *
 * @param mixed  $data
 * @param string $arg_1
 * @param string $arg_2
 * @param string $arg_3
 *
 * @return mixed|void
 */
function give_deprecated_action_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
	$give_map_deprecated_actions = give_deprecated_actions();
	$action = current_filter();

	if ( isset( $give_map_deprecated_actions[ $action ] ) ) {
		if ( has_action( $give_map_deprecated_actions[ $action ] ) ) {
			do_action( $give_map_deprecated_actions[ $action ], $data, $arg_1, $arg_2, $arg_3 );

			if ( ! defined( 'DOING_AJAX' ) ) {
				_give_deprecated_function(
					sprintf(
						/* translators: %s: action name */
						__( 'The %s action' ),
						$give_map_deprecated_actions[ $action ]
					),
					'1.7',
					$action
				);
			}
		}
	}
}
