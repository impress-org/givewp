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

	// Exit if not give embed page.
	if ( 'give-embed' !== get_query_var( 'name' ) || empty( get_query_var( 'give_form_id' ) ) ) {
		return;
	}

	// Setup global post.
	$post = get_post( get_query_var( 'give_form_id' ) );

	nocache_headers();
	header( 'HTTP/1.1 200 OK' );
	require_once 'view/embed-form.php';
	exit();
}

add_action( 'template_redirect', 'give_form_styles_routes' );
