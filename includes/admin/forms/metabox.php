<?php
/**
 * Metabox Functions
 *
 * @package     Give
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add Shortcode Copy Field to Publish Metabox
 *
 * @since: 1.0
 */
function give_add_shortcode_to_publish_metabox() {

	if ( 'give_forms' !== get_post_type() ) {
		return false;
	}
	global $post;

	//Only enqueue scripts for CPT on post type screen
	if ( 'give_forms' === $post->post_type ) {
		//Shortcode column with select all input
		$shortcode = sprintf( '[give_form id="%s"]', absint( $post->ID ) );
		printf(
			'<div class="misc-pub-section"><button type="button" class="button hint-tooltip hint--top js-give-shortcode-button" aria-label="%1$s" data-give-shortcode="%2$s"><span class="dashicons dashicons-admin-page"></span> %3$s</button></div>',
			esc_attr( $shortcode ),
			esc_attr( $shortcode ),
			esc_html__( 'Copy Shortcode', 'give' )
		);
	}

}

add_action( 'post_submitbox_misc_actions', 'give_add_shortcode_to_publish_metabox' );
