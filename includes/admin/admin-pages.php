<?php
/**
 * Admin Pages
 *
 * @package     Give
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2015, WordImpress
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
 * @global $give_campaigns_page
 *
 * @return void
 */
function give_add_options_links() {
	global $give_settings_page, $give_payments_page, $give_campaigns_page, $give_reports_page;

	//Campaigns
	//	$give_campaigns      = get_post_type_object( 'give_campaigns' );
	//	$give_campaigns_page = add_submenu_page( 'edit.php?post_type=give_forms', $give_campaigns->labels->menu_name, $give_campaigns->labels->add_new, 'edit_' . $give_campaigns->capability_type . 's', 'post-new.php?post_type=give_campaigns', null );

	//Payments
	$give_payment       = get_post_type_object( 'give_payment' );
	$give_payments_page = add_submenu_page( 'edit.php?post_type=give_forms', $give_payment->labels->name, $give_payment->labels->menu_name, 'edit_give_payments', 'give-payment-history', 'give_payment_history_page' );

	//Reports
	$give_reports_page = add_submenu_page( 'edit.php?post_type=give_forms', __( 'Donation Reports', 'give' ), __( 'Reports', 'give' ), 'view_give_reports', 'give-reports', 'give_reports_page' );

	//Settings
	$give_settings_page = add_submenu_page( 'edit.php?post_type=give_forms', __( 'Give Settings', 'give' ), __( 'Settings', 'give' ), 'manage_give_settings', 'give-settings', array(
		Give()->give_settings,
		'admin_page_display'
	) );


	$give_add_ons_page = add_submenu_page( 'edit.php?post_type=give_forms', __( 'Give Add-ons', 'give' ), __( 'Add-ons', 'give' ), 'install_plugins', 'give-addons', 'give_add_ons_page' );


}

add_action( 'admin_menu', 'give_add_options_links', 10 );

/**
 *  Determines whether the current admin page is an admin page.
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

	global $pagenow, $typenow, $give_settings_page, $give_payments_page, $give_campaigns_page, $give_reports_page, $give_add_ons_page;

	if ( 'give_forms' == $typenow || 'give_campaigns' == $typenow || 'index.php' == $pagenow || 'post-new.php' == $pagenow || 'post.php' == $pagenow ) {
		return true;
	}

	$give_admin_pages = apply_filters( 'give_admin_pages', array(
		$give_settings_page,
		$give_payments_page,
		$give_campaigns_page,
		$give_reports_page,
		$give_add_ons_page
	) );

	if ( in_array( $pagenow, $give_admin_pages ) ) {
		return true;
	} else {
		return false;
	}

}
