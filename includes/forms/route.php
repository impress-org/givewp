<?php
/**
 * Create a custom url to render give form
 */
function give_rewrites_init() {
	add_rewrite_rule(
		'give-embed/([0-9]+)/?$',
		'index.php?name=give-embed&give_form_id=$matches[1]',
		'top'
	);
}

add_action( 'init', 'give_rewrites_init' );


/**
 * Add custom query var
 *
 * @param $query_vars
 *
 * @return array
 */
function give_query_vars( $query_vars ) {
	$query_vars[] = 'give_form_id';

	return $query_vars;
}

add_filter( 'query_vars', 'give_query_vars' );


/**
 * Load embed template for form
 */
function give_form_styles_routes() {
	global $post;

	if ( give_is_viewing_embed_form() ) {
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
		give_is_viewing_embed_form_receipt()
		|| give_is_viewing_embed_form_failed_transaction_page()
	) {
		require_once 'view/receipt.php';
		exit();
	}
}

add_action( 'template_redirect', 'give_form_styles_routes', 0 );


/**
 * Add class
 *
 * @param array $classes
 *
 * @return array
 */
function give_add_embed_form_class( $classes ) {
	if ( give_is_viewing_embed_form() ) {
		$classes[] = 'give-embed-form';

		if ( ! empty( $_GET['iframe'] ) ) {
			$classes[] = 'give-viewing-form-in-iframe';
		}
	}

	return $classes;
}

add_filter( 'give_form_wrap_classes', 'give_add_embed_form_class' );

/**
 * Add hidden fields
 *
 * @param int   $form_id
 * @param array $args
 */
function give_embed_form_hidden_data( $form_id, $args ) {
	if ( ! give_is_viewing_embed_form() ) {
		return;
	}

	printf( '<input type="hidden" name="%1$s" value="%2$s">', 'give_embed_form', '1' );
}
add_action( 'give_hidden_fields_after', 'give_embed_form_hidden_data', 10, 2 );


/**
 * Edit success page if process embed form
 *
 * @param string $success_page
 *
 * @return string
 */
function give_embed_form_success_uri( $success_page ) {
	if ( give_is_viewing_embed_form() || give_is_processing_embed_form() ) {
		$success_page = add_query_arg( array( 'giveDonationAction' => 'showReceipt' ), $success_page );
	}

	return $success_page;
}

/**
 * Setup Embed form related hooks on int hook
 *
 * @since 2.7
 */
function give_embed_form_setup_hooks_on_init() {
	if ( ! give_is_processing_embed_form() ) {
		return;
	}

	add_filter( 'give_get_success_page_uri', 'give_embed_form_success_uri' );

}
add_action( 'init', 'give_embed_form_setup_hooks_on_init', 1, 3 );


/**
 * Redirect to embed form url if processing embed form
 *
 * @param string $redirect
 *
 * @return string
 */
function give_embed_send_back_to_checkout( $redirect ) {
	if ( ! give_is_processing_embed_form() ) {
		return $redirect;
	}

	$url    = explode( '?', $redirect );
	$url[0] = give_embed_form_get_url( absint( $_REQUEST['give-form-id'] ) );

	return implode( '?', $url );
}
add_filter( 'give_send_back_to_checkout', 'give_embed_send_back_to_checkout' );


// @todo: use slug to render donation form
