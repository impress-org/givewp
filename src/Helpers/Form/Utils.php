<?php
namespace Give\Helpers\Form\Utils;

use function Give\Helpers\Form\Theme\getActiveID;
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
 * @since 2.7.0
 *
 * @return bool
 */
function isProcessingForm() {
	$base = Give()->routeForm->getBase();

	return ! empty( $_REQUEST['give_embed_form'] ) ||
		   false !== strpos( wp_get_referer(), "/{$base}/" );
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
 * Get result if we are viewing embed form receipt or not
 *
 * @return bool
 * @since 2.7.0
 */
function isViewingFormFailedPage() {
	return ! empty( $_REQUEST['giveDonationAction'] )
		   && 'failedDonation' === give_clean( $_REQUEST['giveDonationAction'] );
}

/**
 * This function check whether or not given url is of failed page.
 *
 * @param string $url
 *
 * @return string
 * @since 2.7.0
 */
function isFailedPageURL( $url ) {
	$action = getQueryParamFromURL( $url, 'giveDonationAction' );

	return $action && 'failedDonation' === $action;
}

/**
 * This function check whether or not given url is of success page.
 *
 * @param string $url
 *
 * @return string
 * @since 2.7.0
 */
function isSuccessPageURL( $url ) {
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
	$formTemplate = getActiveID( $formID );

	return ! $formTemplate || 'legacy' === getActiveID( $formID );
}


/**
 * This function will create failed transaction page URL.
 *
 * @since 2.7.0
 * @param array       $args
 * @param string|null $url
 *
 * @return string
 */
function createFailedPageURL( $url = null, $args = [] ) {
	$url  = $url ?: give_get_failed_transaction_uri( $args );
	$args = array_merge( $args, [ 'giveDonationAction' => 'failedDonation' ] );

	return add_query_arg(
		$args,
		$url
	);
}

/**
 * This function will create success page URL.
 *
 * @since 2.7.0
 * @param array       $args
 * @param string|null $url
 *
 * @return string
 */
function createSuccessPageURL( $url = null, $args = [] ) {
	$url  = $url ?: give_get_success_page_uri();
	$args = array_merge( $args, [ 'giveDonationAction' => 'ShowReceipt' ] );

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
	return ! empty( give_clean( $_GET['iframe'] ) );
}
