<?php
namespace Give\Helpers\Form\Utils;

/**
 * Get result if we are viewing embed form or not
 *
 * @since 2.7.0
 *
 * @return bool
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
 * Get success page url.
 *
 * @param array $args
 *
 * @return string
 * @since 2.7.0
 */
function getSuccessPageURL( $args = array() ) {
	return add_query_arg(
		array_merge( array( 'giveDonationAction' => 'showReceipt' ), $args ),
		give_clean( $_REQUEST['give-current-url'] )
	);
}

/**
 * Get success page url.
 *
 * @param array $args
 *
 * @return string
 * @since 2.7
 */
function getFailedTransactionPageURL( $args = array() ) {
	return add_query_arg(
		array_merge( array( 'giveDonationAction' => 'failedDonation' ), $args ),
		give_clean( $_REQUEST['give-current-url'] )
	);
}

