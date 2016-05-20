<?php
/**
 * Admin Footer
 *
 * @package     Give
 * @subpackage  Admin/Footer
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add rating links to the admin dashboard
 *
 * @since        1.0
 * @global        string $typenow
 *
 * @param       string   $footer_text The existing footer text
 *
 * @return      string
 */
function give_admin_rate_us( $footer_text ) {
	global $typenow;

	if ( $typenow == 'give_forms' ) {
		$rate_text = sprintf( __( 'If you like <strong>Give</strong> please leave us a %s&#9733;&#9733;&#9733;&#9733;&#9733;%s rating. It takes a minute and helps a lot. Thanks in advance!', 'give' ), '<a href="https://wordpress.org/support/view/plugin-reviews/give?filter=5#postform" target="_blank" class="give-rating-link" data-rated="' . __( 'Thanks :)', 'give' ) . '">', '</a>' );

		return $rate_text;
	} else {
		return $footer_text;
	}
}

add_filter( 'admin_footer_text', 'give_admin_rate_us' );
