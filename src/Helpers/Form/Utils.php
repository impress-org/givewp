<?php
namespace Give\Helpers\Form\Utils;

use WP_Post;/**
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

/**
 * Get shortcode argument.
 *
 * @since 2.7.0
 */
function getShortcodeArgs() {
	if ( ! isViewingForm() ) {
		return false;
	}

	$queryString = array_map( 'give_clean', wp_parse_args( $_SERVER['QUERY_STRING'] ) );

	return array_intersect_key( $queryString, give_get_default_form_shortcode_args() );
}

/**
 * Get form ID.
 *
 * @global WP_Post $post
 * @return int|null
 * @since 2.7.0
 */
function getFormId() {
	global $post;

	// Get form id from current page
	if ( 'give_forms' === get_post_type( $post ) ) {
		return $post->ID;
	}

	// Get form id from donor purchase session.
	$donorSession = give_get_purchase_session();
	$formId       = ! empty( $donorSession['post_data']['give-form-id'] ) ?
		absint( $donorSession['post_data']['give-form-id'] ) :
		null;

	if ( $formId ) {
		return $formId;
	}

	return null;
}

