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
		'give_pre_process_donation'                => 'give_pre_process_purchase',
		'give_complete_donation'                   => 'give_complete_purchase',
		'give_ajax_donation_errors'                => 'give_ajax_checkout_errors',
		'give_admin_donation_email'                => 'give_admin_sale_notice',
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
				_give_deprecated_function(
					sprintf(
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
