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

	/**
	 * Remove actions
	 */
	// Remove goal.
	remove_action( 'give_pre_form', 'give_show_goal_progress', 10 );
	// Hide title.
	add_filter( 'give_form_title', '__return_empty_string' );
}
add_action( 'give_pre_form_output', 'give_elegent_setup_hooks', 1, 3 );
