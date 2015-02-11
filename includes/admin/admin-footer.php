<?php
/**
 * Admin Footer
 *
 * @package     Give
 * @subpackage  Admin/Footer
 * @copyright   Copyright (c) 2015, WordImpress
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
		$rate_text = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">Give</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'edd' ),
			'https://wordimpress.com',
			'https://wordpress.org/support/view/plugin-reviews/give?filter=5#postform'
		);

		return str_replace( '</span>', '', $footer_text ) . ' | ' . $rate_text . '</span>';
	} else {
		return $footer_text;
	}
}

add_filter( 'admin_footer_text', 'give_admin_rate_us' );
