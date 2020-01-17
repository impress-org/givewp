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
	// Exit if not give embed page.
	if ( 'give-embed' !== get_query_var( 'name' ) || empty( get_query_var( 'give_form_id' ) ) ) {
		return;
	}

	nocache_headers();
	header( 'HTTP/1.1 200 OK' );
	require_once 'view/embed-form.php';
	exit();
}

add_action( 'template_redirect', 'give_form_styles_routes' );

/**
 * Remove all scripts from iFrame environment besides those containing the word "give" when enqueued.
 */
function give_form_styles_remove_non_give_scripts() {
	global $wp_scripts, $wp_styles;

	// Exit if not give embed page.
	if ( 'give-embed' !== get_query_var( 'name' ) || empty( get_query_var( 'give_form_id' ) ) ) {
		return;
	}

	// Runs through the queue scripts
	foreach ( $wp_scripts->queue as $handle ) :
		if ( strpos( $handle, 'give' ) === false ) {
			wp_dequeue_script( $handle );
		}
	endforeach;

	// Runs through the queue styles
	foreach ( $wp_styles->queue as $handle ) :
		if ( strpos( $handle, 'give' ) === false ) {
			wp_dequeue_style( $handle );
		}
	endforeach;

}

add_action( 'wp_enqueue_scripts', 'give_form_styles_remove_non_give_scripts', 99999 );
