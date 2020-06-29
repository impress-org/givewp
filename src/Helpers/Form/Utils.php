<?php
namespace Give\Helpers\Form;

use Give\Controller\Form;
use Give\Helpers\Form\Template\Utils\Frontend;
use Give\Helpers\Utils as GlobalUtils;

class Utils {
	/**
	 * Get result if we are viewing embed form or not
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public static function isViewingForm() {
		$base = Give()->routeForm->getBase();

		return (
			$base === get_query_var( 'url_prefix' ) ||
			( wp_doing_ajax() && false !== strpos( wp_get_referer(), "/{$base}/" ) ) // for ajax
		);
	}

	/**
	 * Get result if we are processing embed form or not
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public static function isProcessingForm() {
		$base     = Give()->routeForm->getBase();
		$formName = get_post_field( 'post_name', Frontend::getFormId() );
		$referer  = trailingslashit( wp_get_referer() ) ?: '';

		return ! empty( $_REQUEST['give_embed_form'] ) ||
			   false !== strpos( $referer, "/{$base}/{$formName}/" ) ||
			   self::inIframe() ||
			   false !== strpos( $referer, 'giveDonationFormInIframe' );
	}

	/**
	 * Get result whether or not performing Give core action on ajax or not.
	 *
	 * @since 2.7.0
	 * @return bool
	 */
	public static function isProcessingGiveActionOnAjax() {
		$action            = isset( $_REQUEST['action'] ) ? give_clean( $_REQUEST['action'] ) : '';
		$whiteListedAction = [ 'get_receipt' ];
		return $action && wp_doing_ajax() && ( 0 === strpos( $action, 'give_' ) || in_array( $action, $whiteListedAction, true ) );
	}

	/**
	 * Get result whether or not show failed transaction error.
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public static function canShowFailedDonationError() {
		return ! empty( $_REQUEST['showFailedDonationError'] );
	}

	/**
	 * This function check whether or not given url is of iframe parent failed page.
	 *
	 * @param string $url
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public static function isIframeParentFailedPageURL( $url ) {
		$action = GlobalUtils::getQueryParamFromURL( $url, 'giveDonationAction' );

		return $action && 'failedDonation' === $action;
	}

	/**
	 * This function check whether or not given url is of iframe parent success page.
	 *
	 * @param string $url
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public static function isIframeParentSuccessPageURL( $url ) {
		$action = GlobalUtils::getQueryParamFromURL( $url, 'giveDonationAction' );

		return $action && 'showReceipt' === $action;
	}

	/**
	 * This function will create success page URL.
	 *
	 * Note: this function is use to get success page url for parent page when perform donation with off-site checkout.
	 *
	 * @since 2.7.0
	 * @param array       $args
	 * @param string|null $url
	 *
	 * @return string
	 */
	public static function createSuccessPageURL( $url, $args = [] ) {
		$args = array_merge( $args, [ 'giveDonationAction' => 'showReceipt' ] );

		return add_query_arg(
			$args,
			$url
		);
	}

	/**
	 * Get Iframe parent page URL.
	 *
	 * Note: must be use only inside iframe logic.
	 *
	 * @since 2.7.0
	 */
	public static function getIframeParentURL() {
		return isset( $_REQUEST['give-current-url'] ) ? give_clean( $_REQUEST['give-current-url'] ) : '';
	}

	/**
	 * Return legacy failed page url.
	 *
	 * Wrapper function for give_get_failed_transaction_uri and without embed form filter
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public static function getLegacyFailedPageURL() {
		remove_filter( 'give_get_failed_transaction_uri', [ Form::class, 'editFailedPageURI' ] );
		$url = give_get_failed_transaction_uri();
		add_filter( 'give_get_failed_transaction_uri', [ Form::class, 'editFailedPageURI' ] );

		return $url;
	}

	/**
	 * Return success page url.
	 *
	 * Wrapper function for give_get_success_page_uri and without embed form filter.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public static function getSuccessPageURL() {
		remove_filter( 'give_get_success_page_uri', [ Form::class, 'editSuccessPageURI' ] );
		$url = give_get_success_page_uri();
		add_filter( 'give_get_success_page_uri', [ Form::class, 'editSuccessPageURI' ] );

		return $url;
	}

	/**
	 * Get result if we are viewing embed form receipt or not
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public static function isViewingFormReceipt() {
		return give_is_success_page();
	}

	/**
	 * This function will create failed transaction page URL.
	 *
	 * Note: this function is use to get failed page url for parent page when perform donation with off-site checkout.
	 *
	 * @since 2.7.0
	 * @param array       $args
	 * @param string|null $url
	 *
	 * @return string
	 */
	public static function createFailedPageURL( $url, $args = [] ) {
		$args = array_merge( $args, [ 'giveDonationAction' => 'failedDonation' ] );

		return add_query_arg(
			$args,
			$url
		);
	}

	/**
	 * Return if current URL loading in iframe or not.
	 *
	 * @since 2.7.0
	 * @return bool
	 */
	public static function inIframe() {
		return ! empty( $_GET['giveDonationFormInIframe'] );
	}

	/**
	 * Returns whether or not the given form uses the legacy form template
	 *
	 * @param int|null $formID
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public static function isLegacyForm( $formID = null ) {
		$formID       = $formID ?: Frontend::getFormId();
		$formTemplate = Template::getActiveID( $formID );

		return ! $formTemplate || 'legacy' === Template::getActiveID( $formID );
	}

	/**
	 * Return whether or not disable donate now button.
	 *
	 * @since 2.7.0
	 * @return bool
	 */
	public static function canDisableDonationNowButton() {
		return ! empty( $_GET['giveDisableDonateNowButton'] );
	}
}
