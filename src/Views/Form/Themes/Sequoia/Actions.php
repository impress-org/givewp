<?php

namespace Give\Views\Form\Themes\Sequoia;

use Give_Donate_Form;
use function Give\Helpers\Form\Utils\isViewingForm;


/**
 * Class Actions
 *
 * @since 2.7.0
 * @package Give\Form\Themes\Sequoia
 */
class Actions {

	/**
	 * Initialize
	 *
	 * @since 2.7.0
	 */
	public function init() {
		// Exit: donor is not on embed form page
		if ( ! isViewingForm() ) {
			return;
		}

		// Handle personal section html template.
		add_action( 'wp_ajax_give_cancel_login', array( $this, 'handleCheckoutField' ), 9 );
		add_action( 'wp_ajax_nopriv_give_cancel_login', array( $this, 'handleCheckoutField' ), 9 );
		add_action( 'wp_ajax_nopriv_give_checkout_register', array( $this, 'handleCheckoutField' ), 9 );

		// Handle common hooks.
		add_action( 'give_donation_form', array( $this, 'loadCommonHooks' ), 9, 2 );

		// Setup hooks.
		add_action( 'give_pre_form_output', array( $this, 'loadHooks' ), 1, 3 );
	}

	/**
	 * Load Checkout Fields
	 *
	 * @since 2.7.0
	 * @return void
	 */
	public function handleCheckoutField() {
		add_action( 'give_donation_form_before_personal_info', array( $this, 'getIntroductionSection' ) );
	}

	/**
	 * Setup common hooks
	 *
	 * @param int   $form_id
	 * @param array $args
	 */
	public function loadCommonHooks( $form_id, $args ) {
		remove_action( 'give_donation_form_register_login_fields', 'give_show_register_login_fields' );
	}

	/**
	 * Setup hooks
	 *
	 * @param int              $form_id
	 * @param array            $args
	 * @param Give_Donate_Form $form
	 */
	public function loadHooks( $form_id, $args, $form ) {
		/**
		 * Add hooks
		 */
		add_action( 'give_pre_form', array( $this, 'getIntroductionSection' ), 12, 3 );
		add_action( 'give_pre_form', array( $this, 'getStatsSection' ), 13, 3 );
		add_action( 'give_pre_form', array( $this, 'getProgressBarSection' ), 14, 3 );
		add_action( 'give_post_form', array( $this, 'getNextButton' ), 13, 3 );
		add_action( 'give_donation_form_top', array( $this, 'getStartWrapperHTMLForAmountSection' ), 0 );
		add_action( 'give_donation_form_top', array( $this, 'getCloseWrapperHTMLForAmountSection' ), 99998 );
		add_action( 'give_payment_mode_top', 'give_show_register_login_fields' );
		add_action( 'give_donation_form_before_personal_info', array( $this, 'getIntroductionSectionTextSubSection' ) );

		/**
		 * Remove actions
		 */
		// Remove goal.
		remove_action( 'give_pre_form', 'give_show_goal_progress', 10 );

		// Hide title.
		add_filter( 'give_form_title', '__return_empty_string' );

		// Override checkout button
		add_filter( 'give_donation_form_submit_button', array( $this, 'getCheckoutButton' ) );
	}

	/**
	 * Add introduction form section
	 *
	 * @since 2.7.0
	 *
	 * @param $form_id
	 * @param $args
	 * @param $form
	 */
	public function getIntroductionSection( $form_id, $args, $form ) {
		include 'sections/introduction.php';
	}

	/**
	 * Add form stats form section
	 *
	 * @since 2.7.0
	 *
	 * @param $form_id
	 * @param $args
	 * @param $form
	 */
	public function getStatsSection( $form_id, $args, $form ) {
		include 'sections/form-income-stats.php';
	}

	/**
	 * Add progress bar form section
	 *
	 * @since 2.7.0
	 *
	 * @param $form_id
	 * @param $args
	 * @param $form
	 */
	public function getProgressBarSection( $form_id, $args, $form ) {
		include 'sections/progress-bar.php';
	}


	/**
	 * Add load next sections button
	 *
	 * @since 2.7.0
	 */
	public function getNextButton( $id ) {

		// Get Theme options
		$theme_options = give_get_meta( $id, '_give_sequoia_form_theme_settings', true, null );

		$label = isset( $theme_options['introduction']['next_label'] ) ? $theme_options['introduction']['next_label'] : __( 'Next', 'give' );
		$color = isset( $theme_options['introduction']['primary_color'] ) ? $theme_options['introduction']['primary_color'] : '#2bc253';

		printf(
			'<div class="give-show-form give-showing__introduction-section"><button class="give-btn" style="background: %1$s">%2$s</button></div>',
			$color,
			$label
		);
	}

	/**
	 * Add checkout button
	 *
	 * @since 2.7.0
	 */
	public function getCheckoutButton() {
		$session = give_get_purchase_session();
		$payment = new \Give_Payment( $session['donation_id'] );

		// Get Theme options
		$theme_options = give_get_meta( $payment->form_id, '_give_sequoia_form_theme_settings', true, null );

		$label = isset( $theme_options['payment_information']['checkout_label'] ) ? $theme_options['payment_information']['checkout_label'] : __( 'Donate Now', 'give' );
		$color = isset( $theme_options['introduction']['primary_color'] ) ? $theme_options['introduction']['primary_color'] : '#2bc253';

		$button = '<div class="give-submit-button-wrap give-clearfix">
			<input type="submit" class="give-submit give-btn" style="background: ' . $color . '" id="give-purchase-button" name="give-purchase" value="' . $label . '" data-before-validation-label="Donate Now">
			<span class="give-loading-animation"></span>
		</div>';
		return $button;
	}

	/**
	 * Add introduction text to personal information section
	 *
	 * @since 2.7.0
	 *
	 * @param int $form_id
	 */
	public function getIntroductionSectionTextSubSection( $form_id ) {
		printf(
			'<div class="give-section personal-information-text"><div class="heading">%1$s</div><div class="subheading">%2$s</div></div>',
			__( 'Tell us a bit amount yourself', 'give' ),
			__( 'We\'ll never share this information with anyone', 'give' )
		);
	}

	/**
	 * Start choose amount section
	 *
	 * @since 2.7.0
	 */
	public function getStartWrapperHTMLForAmountSection() {
		echo '<div class="give-section choose-amount">';
	}

	/**
	 * Close choose amount section
	 *
	 * @since 2.7.0
	 */
	public function getCloseWrapperHTMLForAmountSection() {
		echo '</div>';
	}

}
