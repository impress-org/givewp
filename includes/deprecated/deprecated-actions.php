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
		// New action hook                               Old action hook.
		'give_donation_form_login_fields'                => 'give_purchase_form_login_fields',
		'give_donation_form_register_login_fields'       => 'give_purchase_form_register_login_fields',
		'give_donation_form_before_register_login'       => 'give_purchase_form_before_register_login',
		'give_donation_form_before_cc_form'              => 'give_purchase_form_before_cc_form',
		'give_donation_form_after_cc_form'               => 'give_purchase_form_after_cc_form',
		'give_donation_form_no_access'                   => 'give_purchase_form_no_access',
		'give_donation_form_register_fields'             => 'give_purchase_form_register_fields',
		'give_donation_form_after_user_info'             => 'give_purchase_form_after_user_info',
		'give_donation_form_before_personal_info'        => 'give_purchase_form_before_personal_info',
		'give_donation_form_before_email'                => 'give_purchase_form_before_email',
		'give_donation_form_after_email'                 => 'give_purchase_form_after_email',
		'give_donation_form_user_info'                   => 'give_purchase_form_user_info',
		'give_donation_form_after_personal_info'         => 'give_purchase_form_after_personal_info',
		'give_donation_form'                             => 'give_purchase_form',
		'give_donation_form_wrap_bottom'                 => 'give_purchase_form_wrap_bottom',
		'give_donation_form_before_submit'               => 'give_purchase_form_before_submit',
		'give_donation_form_after_submit'                => 'give_purchase_form_after_submit',
		'give_donation_history_header_before'            => 'give_purchase_history_header_before',
		'give_donation_history_header_after'             => 'give_purchase_history_header_after',
		'give_donation_history_row_start'                => 'give_purchase_history_row_start',
		'give_donation_history_row_end'                  => 'give_purchase_history_row_end',
		'give_payment_form_top'                          => 'give_purchase_form_top',
		'give_payment_form_bottom'                       => 'give_purchase_form_bottom',
		'give_pre_process_donation'                      => 'give_pre_process_purchase',
		'give_complete_donation'                         => 'give_complete_purchase',
		'give_ajax_donation_errors'                      => 'give_ajax_checkout_errors',
		'give_admin_donation_email'                      => 'give_admin_sale_notice',
		'give_tools_tab_export_content_top'              => 'give_reports_tab_export_content_top',
		'give_tools_tab_export_table_top'                => 'give_reports_tab_export_table_top',
		'give_tools_tab_export_table_bottom'             => 'give_reports_tab_export_table_bottom',
		'give_tools_tab_export_content_bottom'           => 'give_report_tab_export_table_bottom',
		'give_pre_edit_donor'                            => 'give_pre_edit_customer',
		'give_post_edit_donor'                           => 'give_post_edit_customer',
		'give_pre_donor_disconnect_user_id'              => 'give_pre_customer_disconnect_user_id',
		'give_post_donor_disconnect_user_id'             => 'give_post_customer_disconnect_user_id',
		'give_update_donor_email_on_user_update'         => 'give_update_customer_email_on_user_update',
		'give_pre_insert_donor'                          => 'give_pre_insert_customer',
		'give_post_insert_donor'                         => 'give_post_insert_customer',
		'give_donor_pre_create'                          => 'give_customer_pre_create',
		'give_donor_post_create'                         => 'give_customer_post_create',
		'give_donor_pre_update'                          => 'give_customer_pre_update',
		'give_donor_post_update'                         => 'give_customer_post_update',
		'give_donor_pre_attach_payment'                  => 'give_customer_pre_attach_payment',
		'give_donor_post_attach_payment'                 => 'give_customer_post_attach_payment',
		'give_donor_pre_remove_payment'                  => 'give_customer_pre_remove_payment',
		'give_donor_post_remove_payment'                 => 'give_customer_post_remove_payment',
		'give_donor_pre_increase_donation_count'         => 'give_customer_pre_increase_purchase_count',
		'give_donor_post_increase_donation_count'        => 'give_customer_post_increase_purchase_count',
		'give_donor_pre_decrease_donation_count'         => 'give_customer_pre_decrease_purchase_count',
		'give_donor_post_decrease_donation_count'        => 'give_customer_post_decrease_purchase_count',
		'give_donor_pre_increase_value'                  => 'give_customer_pre_increase_value',
		'give_donor_post_increase_value'                 => 'give_customer_post_increase_value',
		'give_donor_pre_decrease_value'                  => 'give_customer_pre_decrease_value',
		'give_donor_post_decrease_value'                 => 'give_customer_post_decrease_value',
		'give_donor_pre_add_note'                        => 'give_customer_pre_add_note',
		'give_donor_post_add_note'                       => 'give_customer_post_add_note',
		'give_donor_pre_add_email'                       => 'give_customer_pre_add_email',
		'give_donor_post_add_email'                      => 'give_customer_post_add_email',
		'give_donor_pre_remove_email'                    => 'give_customer_pre_remove_email',
		'give_donor_post_remove_email'                   => 'give_customer_post_remove_email',
		'give_donor_pre_set_primary_email'               => 'give_customer_pre_set_primary_email',
		'give_donor_post_set_primary_email'              => 'give_customer_post_set_primary_email',
		'give_donation_form_top'                         => 'give_checkout_form_top',
		'give_donation_form_bottom'                      => 'give_checkout_form_bottom',
		'give_donor_delete_top'                          => 'give_customer_delete_top',
		'give_donor_delete_bottom'                       => 'give_customer_delete_bottom',
		'give_donor_delete_inputs'                       => 'give_customer_delete_inputs',
		'give_pre_insert_donor_note'                     => 'give_pre_insert_customer_note',
		'give_pre_delete_donor'                          => 'give_pre_delete_customer',
		'give_post_add_donor_email'                      => 'give_post_add_customer_email',
		'give_update_edited_donation'                    => 'give_update_edited_purchase',
		'give_updated_edited_donation'                   => 'give_updated_edited_purchase',
		'give_pre_complete_donation'                     => 'give_pre_complete_purchase',
		'give_profile_editor_after_email'                => 'give_profile_editor_address',
		'give_pre_refunded_payment'                      => 'give_pre_refund_payment',
		'give_post_refunded_payment'                     => 'give_post_refund_payment',
		'give_view_donation_details_billing_before'      => 'give_view_order_details_billing_before',
		'give_view_donation_details_billing_after'       => 'give_view_order_details_billing_after',
		'give_view_donation_details_main_before'         => 'give_view_order_details_main_before',
		'give_view_donation_details_main_after'          => 'give_view_order_details_main_after',
		'give_view_donation_details_form_top'            => 'give_view_order_details_form_top',
		'give_view_donation_details_form_bottom'         => 'give_view_order_details_form_bottom',
		'give_view_donation_details_before'              => 'give_view_order_details_before',
		'give_view_donation_details_after'               => 'give_view_order_details_after',
		'give_view_donation_details_donor_before'        => 'give_view_order_details_files_after',
		'give_view_donation_details_sidebar_before'      => 'give_view_order_details_sidebar_before',
		'give_view_donation_details_sidebar_after'       => 'give_view_order_details_sidebar_after',
		'give_view_donation_details_totals_before'       => 'give_view_order_details_totals_before',
		'give_view_donation_details_totals_after'        => 'give_view_order_details_totals_after',
		'give_view_donation_details_update_before'       => 'give_view_order_details_update_before',
		'give_view_donation_details_update_after'        => 'give_view_order_details_update_after',
		'give_view_donation_details_payment_meta_before' => 'give_view_order_details_payment_meta_before',
		'give_view_donation_details_payment_meta_after'  => 'give_view_order_details_payment_meta_after',
		'give_view_donation_details_update_inner'        => 'give_view_order_details_update_inner',
		'give_donor_delete'                              => 'give_process_donor_deletion',
		'give_delete_donor'                              => 'give_process_donor_deletion',
		'give_checkout_login_fields_before'              => 'give_donation_form_login_fields_before',
		'give_checkout_login_fields_after'               => 'give_donation_form_login_fields_after',
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
	$action                      = current_filter();

	if ( isset( $give_map_deprecated_actions[ $action ] ) ) {
		if ( has_action( $give_map_deprecated_actions[ $action ] ) ) {
			do_action( $give_map_deprecated_actions[ $action ], $data, $arg_1, $arg_2, $arg_3 );

			if ( ! defined( 'DOING_AJAX' ) ) {
				// translators: %s: action name.
				_give_deprecated_function( sprintf( __( 'The %s action', 'give' ), $give_map_deprecated_actions[ $action ] ), '1.7', $action );
			}
		}
	}
}
