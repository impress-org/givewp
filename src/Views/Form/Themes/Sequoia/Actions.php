<?php

namespace Give\Views\Form\Themes\Sequoia;

use Give_Donate_Form;
use Give_Notices;
use function Give\Helpers\Form\Theme\get as getTheme;
use function Give\Helpers\Form\Theme\getActiveID;
use function Give\Helpers\Form\Utils\isViewingForm;


/**
 * Class Actions
 *
 * @since 2.7.0
 * @package Give\Form\Themes\Sequoia
 */
class Actions {

	protected $themeOptions;

	/**
	 * Initialize
	 *
	 * @since 2.7.0
	 */
	public function init() {
		// Get Theme options
		$this->themeOptions = getTheme();

		// Handle personal section html template.
		add_action( 'wp_ajax_give_cancel_login', [ $this, 'cancelLoginAjaxHanleder' ], 9 );
		add_action( 'wp_ajax_nopriv_give_cancel_login', [ $this, 'cancelLoginAjaxHanleder' ], 9 );
		add_action( 'wp_ajax_nopriv_give_checkout_register', [ $this, 'cancelLoginAjaxHanleder' ], 9 );

		// Handle common hooks.
		add_action( 'give_donation_form', [ $this, 'loadCommonHooks' ], 9, 2 );

		// Setup hooks.
		add_action( 'give_pre_form_output', [ $this, 'loadHooks' ], 1, 3 );
	}

	/**
	 * Hanlde cancel login and checkout register ajax request.
	 *
	 * @since 2.7.0
	 * @return void
	 */
	public function cancelLoginAjaxHanleder() {
		add_action( 'give_donation_form_before_personal_info', [ $this, 'getIntroductionSectionTextSubSection' ] );
	}

	/**
	 * Setup common hooks
	 *
	 * @param int   $formId
	 * @param array $args
	 */
	public function loadCommonHooks( $formId, $args ) {
		remove_action( 'give_donation_form_register_login_fields', 'give_show_register_login_fields' );
	}

	/**
	 * Setup hooks
	 *
	 * @param int              $formId
	 * @param array            $args
	 * @param Give_Donate_Form $form
	 */
	public function loadHooks( $formId, $args, $form ) {
		/**
		 * Add hooks
		 */
		add_action( 'give_pre_form', [ $this, 'getIntroductionSection' ], 12, 3 );
		add_action( 'give_pre_form', [ $this, 'getStatsSection' ], 13, 3 );
		add_action( 'give_pre_form', [ $this, 'getProgressBarSection' ], 14, 3 );
		add_action( 'give_post_form', [ $this, 'getNextButton' ], 13, 3 );
		add_action( 'give_donation_form_top', [ $this, 'getStartWrapperHTMLForAmountSection' ], 0 );
		add_action( 'give_donation_form_top', [ $this, 'getCloseWrapperHTMLForAmountSection' ], 99998 );
		add_action( 'give_payment_mode_top', 'give_show_register_login_fields' );
		add_action( 'give_donation_form_before_personal_info', [ $this, 'getIntroductionSectionTextSubSection' ] );

		/**
		 * Remove actions
		 */
		// Remove goal.
		remove_action( 'give_pre_form', 'give_show_goal_progress', 10 );

		// Remove intermediate continue button which appear when display style set to other then onpage.
		remove_action( 'give_after_donation_levels', 'give_display_checkout_button', 10 );

		// Hide title.
		add_filter( 'give_form_title', '__return_empty_string' );

		// Override checkout button
		add_filter( 'give_donation_form_submit_button', [ $this, 'getCheckoutButton' ] );
	}

	/**
	 * Add introduction form section
	 *
	 * @since 2.7.0
	 *
	 * @param $formId
	 * @param $args
	 * @param $form
	 */
	public function getIntroductionSection( $formId, $args, $form ) {
		include 'sections/introduction.php';
	}

	/**
	 * Add form stats form section
	 *
	 * @since 2.7.0
	 *
	 * @param $formId
	 * @param $args
	 * @param $form
	 */
	public function getStatsSection( $formId, $args, $form ) {
		include 'sections/form-income-stats.php';
	}

	/**
	 * Add progress bar form section
	 *
	 * @since 2.7.0
	 *
	 * @param $formId
	 * @param $args
	 * @param $form
	 */
	public function getProgressBarSection( $formId, $args, $form ) {
		include 'sections/progress-bar.php';
	}


	/**
	 * Add load next sections button
	 *
	 * @since 2.7.0
	 */
	public function getNextButton( $id ) {

		$label = isset( $this->themeOptions['introduction']['next_label'] ) ? $this->themeOptions['introduction']['next_label'] : __( 'Next', 'give' );
		$color = isset( $this->themeOptions['introduction']['primary_color'] ) ? $this->themeOptions['introduction']['primary_color'] : '#2bc253';

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

		$label = isset( $this->themeOptions['payment_information']['checkout_label'] ) ? $this->themeOptions['payment_information']['checkout_label'] : __( 'Donate Now', 'give' );
		$color = isset( $this->themeOptions['introduction']['primary_color'] ) ? $this->themeOptions['introduction']['primary_color'] : '#2bc253';

		return sprintf(
			'<div class="give-submit-button-wrap give-clearfix">
				<input type="submit" class="give-submit give-btn" style="background: %1$s" id="give-purchase-button" name="give-purchase" value="%2$s" data-before-validation-label="Donate Now">
				<span class="give-loading-animation"></span>
			</div>',
			$color,
			$label
		);
	}

	/**
	 * Add introduction text to personal information section
	 *
	 * @since 2.7.0
	 *
	 * @param int $formId
	 */
	public function getIntroductionSectionTextSubSection( $formId ) {
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
