<?php
/**
 * Admin Footer
 *
 * @package     Give
 * @subpackage  Admin/Footer
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add rating links to the admin dashboard
 *
 * @param string $footer_text The existing footer text
 *
 * @return      string
 * @since        1.0
 * @global        string $typenow
 */
function give_admin_rate_us( $footer_text ) {
	global $typenow;

	if ( 'give_forms' === $typenow ) {
		$rate_text = sprintf(
			/* translators: %s: Link to 5 star rating */
			__( 'If you like <strong>GiveWP</strong> please leave us a %s rating. It takes a minute and helps a lot. Thanks in advance!', 'give' ),
			'<a href="https://docs.givewp.com/rate-givewp" target="_blank" class="give-rating-link" style="text-decoration:none;" data-rated="' . esc_attr__( 'Thanks :)', 'give' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
		);

		return $rate_text;
	} else {
		return $footer_text;
	}
}

add_filter( 'admin_footer_text', 'give_admin_rate_us' );
