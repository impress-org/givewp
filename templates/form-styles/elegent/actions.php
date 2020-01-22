<?php
/**
 * Add introduction form section
 *
 * @param $form_id
 * @param $args
 * @param $form
 */
function give_elegent_add_form_introduction_section( $form_id, $args, $form ) {
	include 'sections/introduction.php';
}

/**
 * Add form stats form section
 *
 * @param $form_id
 * @param $args
 * @param $form
 */
function give_elegent_add_form_stats_section( $form_id, $args, $form ) {
	include 'sections/form-income-stats.php';
}

/**
 * Add progress bar form section
 *
 * @param $form_id
 * @param $args
 * @param $form
 */
function give_elegent_add_progress_bar_section( $form_id, $args, $form ) {
	include 'sections/progress-bar.php';
}

/**
 * Setup common hooks in-favor of elegent form style
 *
 * @param int              $form_id
 * @param array            $args
 * @param Give_Donate_Form $form
 */
function give_elegent_setup_common_hooks( $form_id, $args, $form ) {
	// Remove personal information from current position.
	remove_action( 'give_donation_form_after_user_info', 'give_user_info_fields' );
	remove_action( 'give_register_fields_before', 'give_user_info_fields' );
}

/**
 * Setup hooks in-favor of elegent form style
 *
 * @param int              $form_id
 * @param array            $args
 * @param Give_Donate_Form $form
 */
function give_elegent_setup_hooks( $form_id, $args, $form ) {
	// early exit.
	if ( ! give_is_viewing_embed_form() ) {
		return;
	}

	/**
	 * Add hooks
	 */
	// Add customized introduction section.
	add_action( 'give_pre_form', 'give_elegent_add_form_introduction_section', 11, 3 );
	add_action( 'give_pre_form', 'give_elegent_add_form_stats_section', 12, 3 );
	add_action( 'give_pre_form', 'give_elegent_add_progress_bar_section', 13, 3 );
	add_action( 'give_payment_mode_top', 'give_user_info_fields' );

	/**
	 * Remove actions
	 */
	// Remove goal.
	remove_action( 'give_pre_form', 'give_show_goal_progress', 10 );
	// Hide title.
	add_filter( 'give_form_title', '__return_empty_string' );

	// Setup common hooks.
	give_elegent_setup_common_hooks( $form_id, $args, $form );
}
add_action( 'give_pre_form_output', 'give_elegent_setup_hooks', 1, 3 );

/**
 * Setup hooks in-favor of elegent form style when donor changes payment gateway
 *
 * @param int $form_id
 */
function give_elegent_setup_hooks_on_ajax( $form_id ) {
	give_elegent_setup_common_hooks( $form_id, array(), new Give_Donate_Form( $form_id ) );
}
add_action( 'wp_ajax_give_load_gateway', 'give_elegent_setup_hooks_on_ajax', 9 );
add_action( 'wp_ajax_no_privgive_load_gateway', 'give_elegent_setup_hooks_on_ajax, 9' );


// @todo: add remove hooks on basis on form style or mode embed or onpage
