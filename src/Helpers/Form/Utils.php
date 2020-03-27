<?php
namespace Give\Helpers\Form\Utils;

use function Give\Helpers\Form\Theme\getActiveID;

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
	return ! empty( $_REQUEST['give_embed_form'] );
}


/**
 * Get result if we are viewing embed form receipt or not
 *
 * @return bool
 * @since 2.7.0
 */
function isViewingFormReceipt() {
	return ! empty( $_REQUEST['giveDonationAction'] )
		   && 'showReceipt' === give_clean( $_REQUEST['giveDonationAction'] )
		   && give_is_success_page();
}

/**
 * Get result if we are viewing embed form receipt or not
 *
 * @return bool
 * @since 2.7.0
 */
function isViewingFormFailedTransactionPage() {
	return ! empty( $_REQUEST['giveDonationAction'] )
		   && 'failedDonation' === give_clean( $_REQUEST['giveDonationAction'] )
		   && give_is_failed_transaction_page();
}

/**
 * This function check whether or not given url is of failed page.
 *
 * @param string $url
 *
 * @return string
 * @since 2.7.0
 */
function isFailedTransactionPageURL( $url ) {
	$failedPageURL = trailingslashit( get_permalink( give_get_option( 'failure_page', 0 ) ) );

	return 0 === strpos( $url, $failedPageURL );
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
	$failedPageURL = trailingslashit( get_permalink( give_get_option( 'success_page', 0 ) ) );

	return 0 === strpos( $url, $failedPageURL );
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

