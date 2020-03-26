<?php

/**
 * Handle Embed Donation Form Route
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Controller;

use Give\Form\LoadTheme;
use WP_Post;
use function Give\Helpers\Form\Theme\Utils\Frontend\getFormId;
use function Give\Helpers\Form\Theme\Utils\Frontend\getShortcodeArgs;
use function Give\Helpers\Form\Utils\isLegacyForm;
use function Give\Helpers\Form\Utils\isProcessingForm;
use function Give\Helpers\Form\Utils\isViewingForm;
use function Give\Helpers\Form\Utils\isViewingFormFailedTransactionPage;
use function Give\Helpers\Form\Utils\isViewingFormReceipt;

defined( 'ABSPATH' ) || exit;

/**
 * Theme class.
 *
 * @since 2.7.0
 */
class Form {

	/**
	 * Initialize
	 *
	 * @since 2.7.0
	 */
	public function init() {
		add_action( 'template_redirect', [ $this, 'load' ], 0 );
		add_action( 'init', [ $this, 'loadThemeOnAjaxRequest' ] );
		add_action( 'init', [ $this, 'embedFormSuccessURIHandler' ], 1, 3 );
		add_filter( 'give_send_back_to_checkout', [ $this, 'handlePrePaymentProcessingErrorRedirect' ] );
		add_action( 'give_before_single_form_summary', [ $this, 'handleSingleDonationFormPage' ], 0 );
	}

	/**
	 * Load view
	 *
	 * @since 2.7.0
	 * @global WP_Post $post
	 */
	public function load() {
		$isViewingForm    = isViewingForm();
		$isViewingReceipt = isViewingFormReceipt() || isViewingFormFailedTransactionPage();
		$action           = ! empty( $_REQUEST['giveDonationAction'] ) ? give_clean( $_REQUEST['giveDonationAction'] ) : '';

		// Exit: we are not on embed form's main page or receipt page.
		if ( ! ( $isViewingForm || $isViewingReceipt ) ) {
			return;
		}

		// Exit: redirect donor to receipt or fail transaction page.
		if (
			! empty( $_REQUEST['giveDonationAction'] ) &&
			$isViewingForm &&
			$action &&
			in_array( $action, [ 'showReceipt', 'failedDonation' ] )
		) {
			if ( 'showReceipt' === give_clean( $_REQUEST['giveDonationAction'] ) ) {
				wp_redirect( give_get_success_page_url( '?giveDonationAction=showReceipt' ) );
			} elseif ( 'failedDonation' === give_clean( $_REQUEST['giveDonationAction'] ) ) {
				wp_redirect( give_get_failed_transaction_uri( '?giveDonationAction=failedDonation' ) );
			}

			exit();
		}

		// Set header.
		nocache_headers();
		header( 'HTTP/1.1 200 OK' );

		if ( $isViewingForm ) {
			$shortcodeArgs = getShortcodeArgs();
			$this->setupGlobalPost();

			require_once $this->loadTheme()
							  ->getTheme()
							  ->getTemplate( 'form' );

			exit();
		}

		if ( $isViewingReceipt ) {
			require_once $this->loadTheme()
							  ->getTheme()
							  ->getTemplate( 'receipt' );
			exit();
		}
	}


	/**
	 * Load form theme
	 *
	 * @return LoadTheme
	 * @since 2.7.0
	 */
	private function loadTheme() {
		$themeLoader = new LoadTheme();
		$themeLoader->init();

		return $themeLoader;
	}


	/**
	 * Setup global $post
	 *
	 * @global WP_Post $post
	 */
	private function setupGlobalPost() {
		global $post;

		$form = get_query_var( 'give_form_id' );

		// Get post.
		$post = current(
			get_posts(
				[
					'post_type'   => 'give_forms',
					'name'        => get_query_var( 'give_form_id' ),
					'numberposts' => 1,
				]
			)
		);

		if ( ! $form || null === $post ) {
			wp_die( __( 'Donation form does not exist', 'give' ) );
		}
	}


	/**
	 * Load theme on ajax request.
	 *
	 * @since 2.7.0
	 */
	public function loadThemeOnAjaxRequest() {
		if (
			defined( 'DOING_AJAX' ) &&
			isset( $_REQUEST['action'] ) &&
			0 === strpos( $_REQUEST['action'], 'give_' )
		) {
			global $post;

			// Get form ID.
			if ( isset( $_REQUEST['give_form_id'] ) ) {
				$formID = absint( $_REQUEST['give_form_id'] );
			} elseif ( isset( $_REQUEST['form_id'] ) ) {
				$formID = absint( $_REQUEST['form_id'] );
			} else {
				return;
			}

			$post        = get_post( $formID );
			$themeLoader = new LoadTheme();
			$themeLoader->init();
		}
	}


	/**
	 * Add filter to success page url.
	 *
	 * @since 2.7.0
	 */
	public function embedFormSuccessURIHandler() {
		if ( ! isProcessingForm() ) {
			return;
		}

		add_filter( 'give_get_success_page_uri', [ $this, 'addQueryParamsToSuccessURI' ] );
	}


	/**
	 * Add query param to success page
	 *
	 * @since 2.7.0
	 * @param string $successPage
	 *
	 * @return string
	 */
	public function addQueryParamsToSuccessURI( $successPage ) {
		return add_query_arg( [ 'giveDonationAction' => 'showReceipt' ], $successPage );
	}

	/**
	 * Handle pre payment processing redirect.
	 *
	 * @since 2.7.0
	 * @param string $redirect
	 *
	 * @return string
	 */
	public function handlePrePaymentProcessingErrorRedirect( $redirect ) {
		if ( ! isProcessingForm() ) {
			return $redirect;
		}

		$url    = explode( '?', $redirect );
		$url[0] = Give()->routeForm->getURL( absint( $_REQUEST['give-form-id'] ) );

		return implode( '?', $url );
	}

	/**
	 * Handle single donation form page.
	 *
	 * @since 2.7.0
	 */
	public function handleSingleDonationFormPage() {
		// Exit if current form is legacy
		if ( isLegacyForm() ) {
			return;
		}

		// Disable sidebar.
		add_action( 'give_get_option_form_sidebar', [ $this, 'disableLegacyDonationFormSidebar' ] );

		// Remove title.
		remove_action( 'give_single_form_summary', 'give_template_single_title', 5 );

		// Remove donation form renderer.
		remove_action( 'give_single_form_summary', 'give_get_donation_form', 10 );

		add_action( 'give_single_form_summary', [ $this, 'renderFormOnSingleDonationFormPage' ], 10 );
	}

	/**
	 * Return 'disabled' as donation form sidebar status.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function disableLegacyDonationFormSidebar() {
		return 'disabled';
	}


	/**
	 * This function handle donation form style for single donation page.
	 *
	 * Note: it will render style on basis on selected form template.
	 *
	 * @since 2.7.0
	 */
	public function renderFormOnSingleDonationFormPage() {
		echo give_form_shortcode( [] );
	}
}
