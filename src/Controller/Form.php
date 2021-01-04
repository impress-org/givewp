<?php

/**
 * Handle Embed Donation Form Route
 *
 * @package Give/Coontroller
 * @since 2.7.0
 */

namespace Give\Controller;

use Give\Form\LoadTemplate;
use Give\Form\Template;
use Give\Helpers\Frontend\Shortcode as ShortcodeUtils;
use Give\Helpers\Frontend\ConfirmDonation;
use Give\Helpers\Utils;
use Give\Helpers\Form\Utils as FormUtils;
use Give\Session\SessionDonation\DonationAccessor;
use Give_Notices;
use WP_Post;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give\Helpers\Form\Template\Utils\Frontend as FrontendFormTemplateUtils;

defined( 'ABSPATH' ) || exit;

/**
 * Form class.
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
		add_action( 'wp', [ $this, 'loadTemplateOnFrontend' ], 11, 0 );
		add_action( 'admin_init', [ $this, 'loadTemplateOnAjaxRequest' ] );
		add_action( 'init', [ $this, 'embedFormRedirectURIHandler' ], 1 );
		add_action( 'template_redirect', [ $this, 'loadReceiptView' ], 1 );
		add_action( 'give_before_single_form_summary', [ $this, 'handleSingleDonationFormPage' ], 0 );
	}

	/**
	 * Load form template on frontend.
	 *
	 * @since 2.7.0
	 */
	public function loadTemplateOnFrontend() {
		if ( FormUtils::isProcessingForm() ) {
			$this->loadTemplate();

			add_action( 'template_redirect', [ $this, 'loadDonationFormView' ], 1 );
		}
	}

	/**
	 * Load receipt view.
	 *
	 * @since 2.7.0
	 */
	public function loadReceiptView() {
		// Do not handle legacy donation form.
		if ( FormUtils::isLegacyForm() ) {
			return;
		}

		// Handle success page.
		if ( FormUtils::isViewingFormReceipt() && ! FormUtils::isLegacyForm() ) {
			/* @var Template $formTemplate */
			$formTemplate = Give()->templates->getTemplate();

			if ( FormUtils::inIframe() || ( $formTemplate->openSuccessPageInIframe && FormUtils::isProcessingForm() ) ) {
				// Set header.
				nocache_headers();
				header( 'HTTP/1.1 200 OK' );

				// Show donation processing template.
				if ( ConfirmDonation::isConfirming() ) {
					$session    = new DonationAccessor();
					$donationId = $session->getDonationId();

					/**
					 * Fire the action hook.
					 *
					 * If developer wants to verify payment before showing receipt then use `give_handle_donation_confirm` action hook to verify donation.
					 * Developer can access query parameters return by payment gateway. for example
					 * $session = new DonationAccessor();
					 * $session->getByKey( "postDataFor{$paymentGatewayId}" )
					 *
					 * @since 2.7.0
					 * @param int $donationId
					 */
					do_action( 'give_handle_donation_confirmation', $donationId );

					ConfirmDonation::removePostedDataFromDonationSession();

					// Load payment processing view only if donation is in pending status.
					if ( 'pending' === get_post_status( $donationId ) ) {
						include GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormDonationProcessing.php';
						exit();
					}
				}

				// Render receipt with in iframe.
				include $formTemplate->getReceiptView();
				exit();
			}

			// Render receipt on success page in iframe.
			add_filter( 'the_content', [ $this, 'showReceiptInIframeOnSuccessPage' ], 1 );
		}
	}

	/**
	 * Load donation form view.
	 *
	 * @since 2.7.0
	 * @global WP_Post $post
	 */
	public function loadDonationFormView() {
		/* @var Template $formTemplate */
		$formTemplate = Give()->templates->getTemplate();

		// Handle failed donation error.
		if ( FormUtils::canShowFailedDonationError() ) {
			add_action( 'give_pre_form', [ $this, 'setFailedTransactionError' ] );
		}

			// Handle donation form.
		if ( FormUtils::isViewingForm() ) {
			// Set header.
			nocache_headers();
			header( 'HTTP/1.1 200 OK' );

			include $formTemplate->getFormView();
			exit();
		}
	}

	/**
	 * Show failed transaction error on donation form.
	 *
	 * @since 2.7.0
	 */
	public function setFailedTransactionError() {
		Give_Notices::print_frontend_notice(
			Give()->templates->getTemplate( FormTemplateUtils::getActiveID() )->getFailedDonationMessage(),
			true,
			'error'
		);
	}

	/**
	 * Handle receipt shortcode on success page
	 *
	 * @since 2.7.0
	 * @param string $content
	 *
	 * @return string
	 */
	public function showReceiptInIframeOnSuccessPage( $content ) {
		$receiptShortcode = ShortcodeUtils::getReceiptShortcodeFromConfirmationPage();
		$content          = str_replace( $receiptShortcode, give_form_shortcode( [] ), $content );

		return $content;
	}


	/**
	 * Load form template
	 *
	 * @return LoadTemplate
	 * @since 2.7.0
	 */
	private function loadTemplate() {
		$templateLoader = new LoadTemplate();
		$templateLoader->init();

		return $templateLoader;
	}

	/**
	 * Load form template on ajax request.
	 *
	 * @since 2.7.0
	 */
	public function loadTemplateOnAjaxRequest() {
		if ( FormUtils::isProcessingGiveActionOnAjax() && ! FormUtils::isLegacyForm() ) {
			$this->loadTemplate();
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
		if ( FormUtils::isProcessingForm() ) {
			add_filter( 'give_get_success_page_uri', [ self::class, 'editSuccessPageURI' ] );
			add_filter( 'give_get_failed_transaction_uri', [ self::class, 'editFailedPageURI' ] );
			add_filter( 'give_send_back_to_checkout', [ $this, 'handlePrePaymentProcessingErrorRedirect' ] );
			add_filter( 'wp_redirect', [ $this, 'handleOffSiteCheckoutRedirect' ] );
		}
	}


	/**
	 * Return current page aka embed form parent url as success page.
	 *
	 * @param string $url
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public static function editSuccessPageURI( $url ) {
		/* @var Template $template */
		$template = Give()->templates->getTemplate();

		return $template->openSuccessPageInIframe ?
			FormUtils::createSuccessPageURL( Utils::switchRequestedURL( $url, FormUtils::getIframeParentURL() ) ) :
			$url;
	}

	/**
	 * Return current page aka embed form parent url as failed page.
	 *
	 * @param string $url
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public static function editFailedPageURI( $url ) {
		/* @var Template $template */
		$template = Give()->templates->getTemplate( FormTemplateUtils::getActiveID() );

		return $template->openFailedPageInIframe ?
			FormUtils::createFailedPageURL( Utils::switchRequestedURL( $url, FormUtils::getIframeParentURL() ) ) :
			$url;
	}


	/**
	 * Handle pre payment processing redirect.
	 *
	 * These redirects mainly happen when donation form data is not valid.
	 *
	 * @since 2.7.0
	 * @since 2.9.6 Adds giveDonationFormInIframe query param to url
	 *
	 * @param string $redirect
	 *
	 * @return string
	 */
	public function handlePrePaymentProcessingErrorRedirect( $redirect ) {
		$redirect = add_query_arg(
			[
				'showDonationProcessingError' => 1,
				'giveDonationFormInIframe'    => 1,
			],
			$redirect
		);

		$url    = explode( '?', $redirect, 2 );
		$url[0] = Give()->routeForm->getURL( get_post_field( 'post_name', absint( $_REQUEST['give-form-id'] ) ) );

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
		/* @var Template $template */
		$template = Give()->templates->getTemplate();

		// Exit if redirect is on same website.
		if ( 0 === strpos( $location, home_url() ) ) {
			if ( FormUtils::isIframeParentSuccessPageURL( $location ) ) {
				$location = FormUtils::getSuccessPageURL();
				$location = Utils::removeDonationAction( $location );

				// Open link in window?
				if ( ! $template->openSuccessPageInIframe ) {
					$this->openLinkInWindow( $location );
				}

				return $location;
			}

			if ( FormUtils::isIframeParentFailedPageURL( $location ) ) {
				$location = add_query_arg( [ 'showFailedDonationError' => 1 ], $template->getFailedPageURL( FrontendFormTemplateUtils::getFormId() ) );
				$location = Utils::removeDonationAction( $location );

				// Open link in window?
				if ( ! $template->openFailedPageInIframe ) {
					$this->openLinkInWindow( FormUtils::getLegacyFailedPageURL() );
				}

				return $location;
			}

			// Add comment here.
			if (
				( ! $template->openSuccessPageInIframe && 0 === strpos( $location, FormUtils::getSuccessPageURL() ) ) ||
				( ! $template->openFailedPageInIframe && 0 === strpos( $location, FormUtils::getLegacyFailedPageURL() ) )
			) {
				$this->openLinkInWindow( $location );
			}

			return $location;
		}

		$this->openLinkInWindow( $location );
	}


	/**
	 * Handle link opening in window instead of iframe.
	 *
	 * @since 2.7.0
	 * @param string $location
	 */
	private function openLinkInWindow( $location ) {
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
		if ( FormUtils::isLegacyForm() ) {
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
