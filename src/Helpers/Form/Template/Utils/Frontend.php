<?php

namespace Give\Helpers\Form\Template\Utils;

use Give\Session\SessionDonation\DonationAccessor;
use WP_Post;

class Frontend {
	/**
	 * This function will return form id.
	 *
	 * There are two ways to auto detect form id:
	 *   1. If global $post is give_forms post type then we assume that we are on donation form page and return id.
	 *   2. if we are not on donation form page and process donation then we will return form id from submitted donation form data.
	 *   3. if we are not on donation form page then we will get donation form id from session.
	 *
	 * This function can be use in donation processing flow i.e from donation form to receipt/failed transaction
	 *
	 * @return int|null
	 * @global WP_Post $post
	 * @since 2.7.0
	 */
	public static function getFormId() {
		global $post;

		if ( 'give_forms' === get_post_type( $post ) ) {
			return $post->ID;
		}

		if ( $formId = Give()->routeForm->getQueriedFormID() ) {
			return $formId;
		}

		// Check if admin previewing donation form.
		if ( $formId = self::getPreviewDonationFormId() ) {
			return $formId;
		}

		// Get form Id on ajax request.
		if ( isset( $_REQUEST['give_form_id'] ) && ( $formId = absint( $_REQUEST['give_form_id'] ) ) ) {
			return $formId;
		}

		// Get form Id on ajax request.
		if ( isset( $_REQUEST['form_id'] ) && ( $formId = absint( $_REQUEST['form_id'] ) ) ) {
			return $formId;
		}

		// Get form id on ajax request by donation id.
		if (
			! empty( $_REQUEST['donation_id'] ) &&
			( $donationId = absint( $_REQUEST['donation_id'] ) )
		) {
			return give_get_payment_form_id( $donationId );
		}

		// Get form id from donor purchase session.
		$session = new DonationAccessor();
		$formId  = $session->getFormId();

		if ( $formId ) {
			return $formId;
		}

		return null;
	}

	/**
	 * Return form id if admin previewing donation form.
	 * Note: only for internal use. This function can be update or remove in future.
	 *
	 * @return int|null
	 * @since 2.7.0
	 */
	public static function getPreviewDonationFormId() {
		if ( ! current_user_can( 'edit_give_forms' ) ) {
			return null;
		}

		if (
			isset( $_GET['preview'], $_GET['p'], $_GET['post_type'] ) &&
			filter_var( $_GET['preview'], FILTER_VALIDATE_BOOLEAN ) &&
			( 'give_forms' === give_clean( $_GET['post_type'] ) ) &&
			( $formId = absint( $_GET['p'] ) )
		) {
			return $formId;
		}

		return null;
	}
}
