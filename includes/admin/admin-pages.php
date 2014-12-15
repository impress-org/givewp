<?php
/**
 * Admin Pages
 *
 * @package     Give
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2014, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the admin submenu pages under the Give menu and assigns their
 * links to global variables
 *
 * @since 1.0
 *
 * @global $give_settings_page
 * @global $give_payments_page
 *
 * @return void
 */
function give_add_options_link() {
	global $give_settings_page, $give_payments_page;

	$give_payment       = get_post_type_object( 'give_payment' );
	$give_payments_page = add_submenu_page( 'edit.php?post_type=give_forms', $give_payment->labels->name, $give_payment->labels->menu_name, 'edit_give_payments', 'give-payment-history', 'give_payment_history_page' );

	$give_settings_page = add_submenu_page( 'edit.php?post_type=give_forms', __( 'Give Settings', 'give' ), __( 'Settings', 'give' ), 'manage_give_settings', 'give_settings', array(
		Give()->give_settings,
		'admin_page_display'
	) );

}

add_action( 'admin_menu', 'give_add_options_link', 10 );

/**
 *  Determines whether the current admin page is an EDD admin page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook.
 *
 * @since 1.0.0
 * @return bool True if Give admin page.
 */
function give_is_admin_page() {

	if ( ! is_admin() || ! did_action( 'wp_loaded' ) ) {
		return false;
	}

	global $pagenow, $typenow, $give_settings_page, $give_payments_page;

	if ( 'give_forms' == $typenow || 'index.php' == $pagenow || 'post-new.php' == $pagenow || 'post.php' == $pagenow ) {
		return true;
	}

	$give_admin_pages = apply_filters( 'give_admin_pages', array( $give_settings_page, $give_payments_page ) );

	if ( in_array( $pagenow, $give_admin_pages ) ) {
		return true;
	} else {
		return false;
	}

}
