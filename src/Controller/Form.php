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
use function Give\Helpers\Form\Utils\isFailedTransactionPageURL;
use function Give\Helpers\Form\Utils\isLegacyForm;
use function Give\Helpers\Form\Utils\isProcessingForm;
use function Give\Helpers\Form\Utils\isSuccessPageURL;
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
		add_action( 'init', [ $this, 'embedFormRedirectURIHandler' ], 1, 3 );
		add_action( 'give_before_single_form_summary', [ $this, 'handleSingleDonationFormPage' ], 0 );
	}

	/**
	 * Load view
	 *
	 * @since 2.7.0
	 * @global WP_Post $post
	 */
	public function load() {
		$isViewingForm       = isViewingForm();
		$isViewingReceipt    = isViewingFormReceipt();
		$isViewingFailedPage = isViewingFormFailedTransactionPage();
		$canWeOverwrite      = $isViewingForm || $isViewingReceipt || $isViewingFailedPage;
		$action              = ! empty( $_REQUEST['giveDonationAction'] ) ? give_clean( $_REQUEST['giveDonationAction'] ) : '';

		// Exit: we are not on embed form's main page. receipt page, failed page.
		if ( ! $canWeOverwrite ) {
			return;
		}

		// Load form template.
		$loadTheme = $this->loadTheme();

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
				wp_redirect( $loadTheme->getTheme()->getFailedTransactionPageURL() );
			}

			exit();
		}

		// Set header.
		nocache_headers();
		header( 'HTTP/1.1 200 OK' );

		if ( $isViewingForm ) {
			$this->setupGlobalPost();

			require_once $loadTheme->getTheme()
							  ->getTemplate( 'form' );

			exit();
		}

		if ( $isViewingReceipt ) {
			require_once $loadTheme->getTheme()
							  ->getTemplate( 'receipt' );
			exit();
		}

		if ( $isViewingFailedPage ) {
			include GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormFailedTransactionPage.php';
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

			$post = get_post( $formID );

			$this->loadTheme();
		}
	}


	/**
	 * Handle donor redirect when process donations.
	 *
	 * This function handle donor redirect when process donation with offsite checkout and on-site checkout.
	 * Donor will immediately redirect to success page aka receipt page for on-site payment process. That means success page remain same (as set in admin settings).
	 * For offsite checkout donor will redirect to embed form parent page. A query parameter will be add to url giveDonationAction=showReceipt to handle further cases.
	 *
	 * @since 2.7.0
	 */
	public function embedFormRedirectURIHandler() {
		if ( ! isProcessingForm() ) {
			return;
		}

		add_filter( 'give_get_success_page_uri', [ $this, 'editSuccessPageURI' ] );
		add_filter( 'give_get_failed_transaction_uri', [ $this, 'editFailedPageURI' ] );
		add_filter( 'give_send_back_to_checkout', [ $this, 'handlePrePaymentProcessingErrorRedirect' ] );
		add_filter( 'wp_redirect', [ $this, 'handleOffSiteCheckoutRedirect' ] );
	}


	/**
	 * Return current page aka embed form parent url as success page.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function editSuccessPageURI() {
		return add_query_arg(
			[ 'giveDonationAction' => 'showReceipt' ],
			give_clean( $_REQUEST['give-current-url'] )
		);

	}

	/**
	 * Return current page aka embed form parent url as failed page.
	 *
	 * @since 2.7.0
	 * @param string $url
	 *
	 * @return string
	 */
	public function editFailedPageURI( $url ) {
		return add_query_arg(
			[ 'giveDonationAction' => 'failedDonation' ],
			$this->switchRequestedURL(
				$url,
				give_clean( $_REQUEST['give-current-url'] )
			)
		);
	}


	/**
	 * This function will change request url with other url.
	 *
	 * @since 2.7.0
	 *
	 * @param string $location Requested URL.
	 * @param string $url URL.
	 *
	 * @return string
	 */
	private function switchRequestedURL( $location, $url ) {
		$tmp    = explode( '?', $location, 2 );
		$tmp[0] = $url;

		$location = implode( '?', $tmp );

		return $location;
	}


	/**
	 * Return donor success page url.
	 *
	 * @param string $url Success page URL.
	 *
	 * @return string
	 * @since 2.7.0
	 */
	private function getSuccessPageRedirect( $url ) {
		remove_filter( 'give_get_success_page_uri', [ $this, 'editSuccessPageURI' ] );

		$url = $this->switchRequestedURL(
			$url,
			give_get_success_page_uri()
		);

		return $url;
	}

	/**
	 * Return donor failed page url.
	 *
	 * @param string $url Success page URL.
	 *
	 * @return string
	 * @since 2.7.0
	 */
	private function getFailedPageRedirect( $url ) {
		add_filter( 'give_get_failed_transaction_uri', [ $this, 'editFailedPageURI' ] );

		$url = $url = $this->switchRequestedURL(
			$url,
			give_get_failed_transaction_uri()
		);

		return $url;
	}

	/**
	 * Handle pre payment processing redirect.
	 *
	 * These redirects mainly happen when donation form data is not valid.
	 *
	 * @param string $redirect
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public function handlePrePaymentProcessingErrorRedirect( $redirect ) {
		$url    = explode( '?', $redirect, 2 );
		$url[0] = Give()->routeForm->getURL( absint( $_REQUEST['give-form-id'] ) );

		return implode( '?', $url );
	}

	/**
	 * Handle offsite payment checkout.
	 *
	 * In case of offsite checkout, this function will load a intermediate template to redirect embed parent page.
	 *
	 * @since 2.7.0
	 * @param string $location
	 *
	 * @return mixed
	 */
	public function handleOffSiteCheckoutRedirect( $location ) {
		// Exit if redirect is on same website.
		if ( 0 === strpos( $location, home_url() ) ) {
			if ( isSuccessPageURL( $location ) ) {
				return $this->getSuccessPageRedirect( $location );
			}

			if ( isFailedTransactionPageURL( $location ) ) {
				return $this->getFailedPageRedirect( $location );
			}

			return $location;
		}

		include GIVE_PLUGIN_DIR . 'src/Views/Form/defaultRedirectHandlerTemplate.php';
		exit();
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
