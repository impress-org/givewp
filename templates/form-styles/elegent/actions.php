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
 * Add load next sections button
 */
function give_elegent_add_next_button() {
	// @todo: make button text customizable.
	printf(
		'<div class="give-show-form give-showing__introduction-section"><button class="give-btn">%1$s</button></div>',
		__( 'Next', 'give' )
	);
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
	remove_action( 'give_donation_form_register_login_fields', 'give_show_register_login_fields' );
}

/**
 * Add introduction text to personal information section
 *
 * @param int $form_id
 */
function give_elegent_add_personal_information_section_text( $form_id ) {
	printf(
		'<div class="give-section personal-information-text"><div class="heading">%1$s</div><div class="subheading">%2$s</div></div>',
		__( 'Tell us a bit amount yourself', 'give' ),
		__( 'We\'ll never share this information with anyone', 'give' )
	);
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
	add_action( 'give_post_form', 'give_elegent_add_next_button', 13, 3 );
	add_action( 'give_payment_mode_top', 'give_show_register_login_fields' );
	add_action( 'give_donation_form_before_personal_info', 'give_elegent_add_personal_information_section_text' );

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
 * @param int   $form_id
 * @param array $args
 */
function give_elegent_setup_hooks_on_ajax( $form_id, $args ) {
	// Early exit.
	if ( ! give_is_viewing_embed_form() ) {
		return;
	}

	give_elegent_setup_common_hooks( $form_id, $args, new Give_Donate_Form( $form_id ) );
}

add_action( 'give_donation_form', 'give_elegent_setup_hooks_on_ajax', 9, 2 );

/**
 * Load Checkout Fields
 *
 * @return void
 */
function give_elegent_load_checkout_fields() {
	add_action( 'give_donation_form_before_personal_info', 'give_elegent_add_personal_information_section_text' );
}

add_action( 'wp_ajax_give_cancel_login', 'give_elegent_load_checkout_fields', 9 );
add_action( 'wp_ajax_nopriv_give_cancel_login', 'give_elegent_load_checkout_fields', 9 );
add_action( 'wp_ajax_nopriv_give_checkout_register', 'give_elegent_load_checkout_fields', 9 );
