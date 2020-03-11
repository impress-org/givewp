<?php

/**
 * Handle Embed Donation Form Route
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Controller;

use Give\Form\ThemeLoader;
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
		add_action( 'template_redirect', array( $this, 'load' ), 0 );
		add_action( 'init', array( $this, 'loadThemeOnAjaxRequest' ) );
		add_action( 'init', array( $this, 'embedFormSuccessURIHandler' ), 1, 3 );
		add_filter( 'give_send_back_to_checkout', array( $this, 'handlePrePaymentProcessingErrorRedirect' ) );
		add_filter( 'wp_redirect', array( $this, 'handleOffSiteCheckoutRedirect' ) );
	}

	/**
	 * Load view
	 *
	 * @since 2.7.0
	 */
	public function load() {
		global $post;

		if ( isViewingForm() ) {
			nocache_headers();
			header( 'HTTP/1.1 200 OK' );

			if ( ! empty( $_REQUEST['giveDonationAction'] ) ) {
				if ( 'showReceipt' === give_clean( $_REQUEST['giveDonationAction'] ) ) {
					wp_redirect( give_get_success_page_url( '?giveDonationAction=showReceipt' ) );
				} elseif ( 'failedDonation' === give_clean( $_REQUEST['giveDonationAction'] ) ) {
					wp_redirect( give_get_failed_transaction_uri( '?giveDonationAction=failedDonation' ) );
				}
			} else {
				$post = get_post( get_query_var( 'give_form_id' ) );
				require_once GIVE_PLUGIN_DIR . 'src/Views/Form-Themes/defaultFormTemplate.php';
			}

			exit();
		}

		if (
			isViewingFormReceipt()
			|| isViewingFormFailedTransactionPage()
		) {
			require_once GIVE_PLUGIN_DIR . 'src/Views/Form-Themes/defaultFormReceiptTemplate.php';
			exit();
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
			$themeLoader = new ThemeLoader();
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

		add_filter( 'give_get_success_page_uri', array( $this, 'addQueryParamsToSuccessURI' ) );
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
		return add_query_arg( array( 'giveDonationAction' => 'showReceipt' ), $successPage );
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
	 * Handle offsite payment checkout
	 *
	 * @since 2.7.0
	 * @param string $location
	 *
	 * @return mixed
	 */
	public function handleOffSiteCheckoutRedirect( $location ) {
		if ( ! isProcessingForm() ) {
			return $location;
		}
		?>
		<!doctype html>
		<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
				<meta http-equiv="X-UA-Compatible" content="ie=edge">
				<title>Donation Processing...</title>
			</head>
			<body>
				<p style="text-align: center">Processing...</p>
				<a style="font-size: 0" id="link" href="<?php echo esc_js( $location ); ?>" target="_parent">Link</a>
				<script>
					document.getElementById( 'link' ).click();
				</script>
			</body>
		</html>
		<?php

		exit();
	}
}
