<?php
/**
 * Admin Pages
 *
 * @package     Give
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
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
 * @global $give_reports_page
 * @global $give_donors_page
 *
 * @return void
 */
function give_add_options_links() {
	global $give_settings_page, $give_payments_page, $give_reports_page, $give_donors_page, $give_tools_page;

	// Payments
	/* @var WP_Post_Type $give_payment */
	$give_payment       = get_post_type_object( 'give_payment' );
	$give_payments_page = add_submenu_page(
		'edit.php?post_type=give_forms',
		$give_payment->labels->name,
		$give_payment->labels->menu_name,
		'edit_give_payments',
		'give-payment-history',
		'give_payment_history_page'
	);

	// Donors
	$give_donors_page = add_submenu_page(
		'edit.php?post_type=give_forms',
		esc_html__( 'Donors', 'give' ),
		esc_html__( 'Donors', 'give' ),
		'view_give_reports',
		'give-donors',
		'give_donors_page'
	);

	// Settings
	$give_settings_page = add_submenu_page(
		'edit.php?post_type=give_forms',
		esc_html__( 'GiveWP Settings', 'give' ),
		esc_html__( 'Settings', 'give' ),
		'manage_give_settings',
		'give-settings',
		[
			Give()->give_settings,
			'output',
		]
	);

	// Tools.
	$give_tools_page = add_submenu_page(
		'edit.php?post_type=give_forms',
		esc_html__( 'GiveWP Tools', 'give' ),
		esc_html__( 'Tools', 'give' ),
		'manage_give_settings',
		'give-tools',
		[
			Give()->give_settings,
			'output',
		]
	);
}

add_action( 'admin_menu', 'give_add_options_links', 10 );



/**
 * Creates the admin add-ons submenu page under the Give menu and assigns their
 * link to global variable
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_add_add_ons_option_link() {
	global $submenu;

	// Show menu only if user has permission.
	if ( ! current_user_can( 'edit_give_payments' ) ) {
		return;
	}

	// Add-ons
	$submenu['edit.php?post_type=give_forms'][] = [
		esc_html__( 'Add-ons', 'give' ),
		'install_plugins',

		/**
		 * Filter the add-on page url.
		 *
		 * @since 2.6.0
		 */
		apply_filters( 'give_addon_menu_item_url', esc_url( 'http://docs.givewp.com/addons-menu-link' ) ),
	];

}
add_action( 'admin_menu', 'give_add_add_ons_option_link', 999999 );

/**
 *  Determines whether the current admin page is a Give admin page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook.
 *
 * @since 1.0
 * @since 2.1 Simplified function.
 *
 * @param string $passed_page Optional. Main page's slug
 * @param string $passed_view Optional. Page view ( ex: `edit` or `delete` )
 *
 * @return bool True if Give admin page.
 */
function give_is_admin_page( $passed_page = '', $passed_view = '' ) {
	global $pagenow, $typenow;

	$found          = true;
	$get_query_args = ! empty( $_GET ) ? @array_map( 'strtolower', $_GET ) : [];

	// Set default argument, if not passed.
	$query_args = wp_parse_args( $get_query_args, array_fill_keys( [ 'post_type', 'action', 'taxonomy', 'page', 'view', 'tab' ], false ) );

	switch ( $passed_page ) {
		case 'categories':
		case 'tags':
			$has_view = in_array( $passed_view, [ 'list-table', 'edit', 'new' ], true );

			if (
				! in_array( $query_args['taxonomy'], [ 'give_forms_category', 'give_forms_tag' ], true ) &&
				'edit-tags.php' !== $pagenow &&
				(
					$has_view ||
					(
						( in_array( $passed_view, [ 'list-table', 'new' ], true ) && 'edit' === $query_args['action'] ) ||
						( 'edit' !== $passed_view && 'edit' !== $query_args['action'] ) &&
						! $has_view
					)
				)
			) {
				$found = false;
			}
			break;
		// Give Donation form page.
		case 'give_forms':
			$has_view = in_array( $passed_view, [ 'new', 'list-table', 'edit' ], true );

			if (
				'give_forms' !== $typenow &&
				(
					( 'list-table' !== $passed_view && 'edit.php' !== $pagenow ) &&
					( 'edit' !== $passed_view && 'post.php' !== $pagenow ) &&
					( 'new' !== $passed_view && 'post-new.php' !== $pagenow )
				) ||
				(
					! $has_view &&
					( 'post-new.php' !== $pagenow && 'give_forms' !== $query_args['post_type'] )
				)
			) {
				$found = false;
			}
			break;
		// Give Donors page.
		case 'donors':
			$has_view = array_intersect( [ $passed_view, $query_args['view'] ], [ 'list-table', 'overview', 'notes' ] );

			if (
				( 'give-donors' !== $query_args['page'] || 'edit.php' !== $pagenow ) &&
				(
					( $passed_view !== $query_args['view'] || ! empty( $has_view ) ) ||
					( false !== $query_args['view'] && 'list-table' !== $passed_view )
				)
			) {
				$found = false;
			}
			break;
		// Give Donations page.
		case 'payments':
			if (
				( 'give-payment-history' !== $query_args['page'] || 'edit.php' !== $pagenow ) &&
				(
					! in_array( $passed_view, [ 'list-table', 'edit' ], true ) ||
					(
						( 'list-table' !== $passed_view && false !== $query_args['view'] ) ||
						( 'edit' !== $passed_view && 'view-payment-details' !== $query_args['view'] )
					)
				)
			) {
				$found = false;
			}
			break;
		case 'reports':
		case 'settings':
		case 'addons':
			// Get current tab.
			$current_tab       = empty( $passed_view ) ? $query_args['tab'] : $passed_view;
			$give_setting_page = in_array( $query_args['page'], [ 'give-reports', 'give-settings', 'give-addons' ], true );

			// Check if it's Give Setting page or not.
			if (
				( 'edit.php' !== $pagenow || ! $give_setting_page ) &&
				! Give_Admin_Settings::is_setting_page( $current_tab )
			) {
				$found = false;
			}
			break;
		default:
			global $give_payments_page, $give_settings_page, $give_reports_page, $give_system_info_page, $give_settings_export, $give_donors_page, $give_tools_page;
			$admin_pages = apply_filters(
				'give_admin_pages',
				[
					$give_payments_page,
					$give_settings_page,
					$give_reports_page,
					$give_system_info_page,
					$give_settings_export,
					$give_donors_page,
					$give_tools_page,
				]
			);

			$found = ( 'give_forms' === $typenow || in_array( $pagenow, array_merge( $admin_pages, [ 'index.php', 'post-new.php', 'post.php', 'widgets.php', 'customize.php' ] ), true ) ) ? true : false;
	}
	return (bool) apply_filters( 'give_is_admin_page', $found, $query_args['page'], $query_args['view'], $passed_page, $passed_view );
}

/**
 * Add setting tab to give-settings page
 *
 * @since  1.8
 * @param  array $settings
 * @return array
 */
function give_settings_page_pages( $settings ) {
	include 'abstract-admin-settings-page.php';

	$settings = [
		// General settings.
		include GIVE_PLUGIN_DIR . 'includes/admin/settings/class-settings-general.php',

		// Payment Gateways Settings.
		include GIVE_PLUGIN_DIR . 'includes/admin/settings/class-settings-gateways.php',

		// Display settings.
		include GIVE_PLUGIN_DIR . 'includes/admin/settings/class-settings-display.php',

		// Emails settings.
		include GIVE_PLUGIN_DIR . 'includes/admin/settings/class-settings-email.php',

		// Addons settings.
		include GIVE_PLUGIN_DIR . 'includes/admin/settings/class-settings-addon.php',

		// License settings.
		include GIVE_PLUGIN_DIR . 'includes/admin/settings/class-settings-license.php',

		// Advanced settings.
		include GIVE_PLUGIN_DIR . 'includes/admin/settings/class-settings-advanced.php',
	];

	// Output.
	return $settings;
}
add_filter( 'give-settings_get_settings_pages', 'give_settings_page_pages', 0, 1 );


/**
 * Add setting tab to give-settings page
 *
 * @since  1.8
 * @param  array $settings
 * @return array
 */
function give_reports_page_pages( $settings ) {
	include 'abstract-admin-settings-page.php';

	$settings = [
		// Earnings.
		include 'reports/class-earnings-report.php',

		// Forms.
		include 'reports/class-forms-report.php',

		// Gateways.
		include 'reports/class-gateways-report.php',

	];

	// Output.
	return $settings;
}
add_filter( 'give-reports_get_settings_pages', 'give_reports_page_pages', 0, 1 );

/**
 * Add setting tab to give-settings page
 *
 * @since  1.8
 * @param  array $settings
 * @return array
 */
function give_tools_page_pages( $settings ) {
	include 'abstract-admin-settings-page.php';

	$settings = [

		// Export.
		include GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-export.php',

		// Import
		include_once GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-import.php',

		// Logs.
		include GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-logs.php',

		// API.
		include GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-api.php',

		// Data.
		include GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-data.php',

		// System Info.
		include GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-system-info.php',
	];

	// Output.
	return $settings;
}
add_filter( 'give-tools_get_settings_pages', 'give_tools_page_pages', 0, 1 );

/**
 * Set default tools page tab.
 *
 * @since  1.8
 * @param  string $default_tab Default tab name.
 * @return string
 */
function give_set_default_tab_form_tools_page( $default_tab ) {
	return 'export';
}
add_filter( 'give_default_setting_tab_give-tools', 'give_set_default_tab_form_tools_page', 10, 1 );


/**
 * Set default reports page tab.
 *
 * @since  1.8
 * @param  string $default_tab Default tab name.
 * @return string
 */
function give_set_default_tab_form_reports_page( $default_tab ) {
	return 'earnings';
}
add_filter( 'give_default_setting_tab_give-reports', 'give_set_default_tab_form_reports_page', 10, 1 );


/**
 * Add a page display state for special Give pages in the page list table.
 *
 * @since 1.8.18
 *
 * @param array   $post_states An array of post display states.
 * @param WP_Post $post The current post object.
 *
 * @return array
 */
function give_add_display_page_states( $post_states, $post ) {

	switch ( $post->ID ) {
		case give_get_option( 'success_page' ):
			$post_states['give_successfully_page'] = __( 'Donation Success Page', 'give' );
			break;

		case give_get_option( 'failure_page' ):
			$post_states['give_failure_page'] = __( 'Donation Failed Page', 'give' );
			break;

		case give_get_option( 'history_page' ):
			$post_states['give_history_page'] = __( 'Donation History Page', 'give' );
			break;
	}

	return $post_states;
}

// Add a post display state for special Give pages.
add_filter( 'display_post_states', 'give_add_display_page_states', 10, 2 );
