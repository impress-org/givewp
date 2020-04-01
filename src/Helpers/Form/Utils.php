<?php
namespace Give\Helpers\Form\Utils;

use Give\Controller\Form;
use function Give\Helpers\Form\Theme\getActiveID;
use function Give\Helpers\Form\Theme\Utils\Frontend\getFormId;
use function Give\Helpers\getQueryParamFromURL;

/**
 * Get result if we are viewing embed form or not
 *
 * @return bool
 * @since 2.7.0
 */
function isViewingForm() {
	$base = Give()->routeForm->getBase();

	return (
		$base === get_query_var( 'name' ) ||
		( wp_doing_ajax() && false !== strpos( wp_get_referer(), "/{$base}/" ) ) // for ajax
	);
}

/**
 * Get result if we are processing embed form or not
 *
 * @return bool
 * @since 2.7.0
 *
 */
function isProcessingForm() {
	$base     = Give()->routeForm->getBase();
	$formName = get_post_field( 'post_name', getFormId() );

	return ! empty( $_REQUEST['give_embed_form'] ) ||
	       false !== strpos( trailingslashit( wp_get_referer() ), "/{$base}/{$formName}/" ) ||
	       inIframe();
}


/**
 * Get result whether or not performing Give core action on ajax or not.
 *
 * @since 2.7.0
 * @return bool
 */
function isProcessingGiveActionOnAjax(){
	return isset( $_REQUEST['action'] ) &&
	       wp_doing_ajax() &&
	       0 === strpos( $_REQUEST['action'], 'give_' );
}


/**
 * Get result if we are viewing embed form receipt or not
 *
 * @return bool
 * @since 2.7.0
 */
function isViewingFormReceipt() {
	return give_is_success_page();
}

/**
 * Get result whether or not show failed transaction error.
 *
 * @return bool
 * @since 2.7.0
 */
function canShowFailedDonationError() {
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
function isIframeParentFailedPageURL( $url ) {
	$action = getQueryParamFromURL( $url, 'giveDonationAction' );

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
function isIframeParentSuccessPageURL( $url ) {
	$action = getQueryParamFromURL( $url, 'giveDonationAction' );

	return $action && 'showReceipt' === $action;
}


/**
 * Returns whether or not the given form uses the legacy form template
 *
 * @param int|null $formID
 *
 * @return bool
 * @since 2.7.0
 */
function isLegacyForm( $formID = null ) {
	$formID       = $formID ?: getFormId();
	$formTemplate = getActiveID( $formID );

	return ! $formTemplate || 'legacy' === getActiveID( $formID );
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
function createFailedPageURL( $url, $args = [] ) {
	$args = array_merge( $args, [ 'giveDonationAction' => 'failedDonation' ] );

	return add_query_arg(
		$args,
		$url
	);
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
function createSuccessPageURL( $url, $args = [] ) {
	$args = array_merge( $args, [ 'giveDonationAction' => 'showReceipt' ] );

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
function inIframe() {
	return ! empty( $_GET['giveDonationFormInIframe'] );
}


/**
 * Return success page url.
 *
 * Wrapper function for give_get_success_page_uri and without embed form filter.
 *
 * @since 2.7.0
 * @return string
 */
function getSuccessPageURL() {
	remove_filter( 'give_get_success_page_uri', [ Form::class, 'editSuccessPageURI' ] );
	$url = give_get_success_page_uri();
	add_filter( 'give_get_success_page_uri', [ Form::class, 'editSuccessPageURI' ] );

	return $url;
}

/**
 * Return legacy failed page url.
 *
 * Wrapper function for give_get_failed_transaction_uri and without embed form filter
 *
 * @since 2.7.0
 * @return string
 */
function getLegacyFailedPageURL() {
	remove_filter( 'give_get_failed_transaction_uri', [ Form::class, 'editFailedPageURI' ] );
	$url = give_get_failed_transaction_uri();
	add_filter( 'give_get_failed_transaction_uri', [ Form::class, 'editFailedPageURI' ] );

	return $url;
}
