<?php
namespace Give\Helpers\Form\Theme\Utils\Frontend;

use WP_Post;

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
function getFormId() {
	global $post;

	if ( 'give_forms' === get_post_type( $post ) ) {
		return $post->ID;
	}

	if ( $formId = get_query_var( 'give_form_id' ) ) {
		$form = current(
			get_posts(
				[
					'name'        => $formId,
					'numberposts' => 1,
					'post_type'   => 'give_forms',
				]
			)
		);

		return $form->ID;
	}

	// Get form Id on ajax request.
	if ( isset( $_REQUEST['give_form_id'] ) && ( $formId = absint( $_REQUEST['give_form_id'] ) ) ) {
		return $formId;
	}

	// Get form Id on ajax request.
	if ( isset( $_REQUEST['form_id'] ) && ( $formId = absint( $_REQUEST['form_id'] ) ) ) {
		return $formId;
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


