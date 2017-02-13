<?php
/**
 * Admin Pages
 *
 * @package     Give
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, WordImpress
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
 * @global $give_add_ons_page
 * @global $give_upgrades_screen
 * @global $give_donors_page
 *
 * @return void
 */
function give_add_options_links() {
	global $give_settings_page, $give_payments_page, $give_reports_page, $give_add_ons_page, $give_upgrades_screen, $give_donors_page, $give_tools_page;

	//Payments
	$give_payment       = get_post_type_object( 'give_payment' );
	$give_payments_page = add_submenu_page( 'edit.php?post_type=give_forms', $give_payment->labels->name, $give_payment->labels->menu_name, 'edit_give_payments', 'give-payment-history', 'give_payment_history_page' );

	//Donors
	$give_donors_page = add_submenu_page( 'edit.php?post_type=give_forms', esc_html__( 'Donors', 'give' ), esc_html__( 'Donors', 'give' ), 'view_give_reports', 'give-donors', 'give_customers_page' );

	//Reports`
	$give_reports_page = add_submenu_page(
		'edit.php?post_type=give_forms',
		esc_html__( 'Donation Reports', 'give' ),
		esc_html__( 'Reports', 'give' ),
		'view_give_reports',
		'give-reports',
		array(
			Give()->give_settings,
			'output',
		)
	);

	//Settings
	$give_settings_page = add_submenu_page(
		'edit.php?post_type=give_forms',
		esc_html__( 'Give Settings', 'give' ),
		esc_html__( 'Settings', 'give' ),
		'manage_give_settings',
		'give-settings',
		array(
			Give()->give_settings,
			'output',
		)
	);

	//Tools.
	$give_tools_page = add_submenu_page( 'edit.php?post_type=give_forms', esc_html__( 'Give Tools', 'give' ), esc_html__( 'Tools', 'give' ), 'manage_give_settings', 'give-tools', array(
		Give()->give_settings,
		'output'
	) );

	//Add-ons
	$give_add_ons_page = add_submenu_page( 'edit.php?post_type=give_forms', esc_html__( 'Give Add-ons', 'give' ), esc_html__( 'Add-ons', 'give' ), 'install_plugins', 'give-addons', 'give_add_ons_page' );

	//Upgrades
	$give_upgrades_screen = add_submenu_page( null, esc_html__( 'Give Upgrades', 'give' ), esc_html__( 'Give Upgrades', 'give' ), 'manage_give_settings', 'give-upgrades', 'give_upgrades_screen' );


}

add_action( 'admin_menu', 'give_add_options_links', 10 );

/**
 *  Determines whether the current admin page is a Give admin page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook.
 *
 * @since 1.0
 *
 * @param string $passed_page Optional. Main page's slug
 * @param string $passed_view Optional. Page view ( ex: `edit` or `delete` )
 *
 * @return bool True if Give admin page.
 */
function give_is_admin_page( $passed_page = '', $passed_view = '' ) {

	global $pagenow, $typenow;

	$found     = false;
	$post_type = isset( $_GET['post_type'] ) ? strtolower( $_GET['post_type'] ) : false;
	$action    = isset( $_GET['action'] ) ? strtolower( $_GET['action'] ) : false;
	$taxonomy  = isset( $_GET['taxonomy'] ) ? strtolower( $_GET['taxonomy'] ) : false;
	$page      = isset( $_GET['page'] ) ? strtolower( $_GET['page'] ) : false;
	$view      = isset( $_GET['view'] ) ? strtolower( $_GET['view'] ) : false;
	$tab       = isset( $_GET['tab'] ) ? strtolower( $_GET['tab'] ) : false;

	switch ( $passed_page ) {
		case 'give_forms':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'post.php' ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'post-new.php' ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) || 'give_forms' === $post_type || ( 'post-new.php' == $pagenow && 'give_forms' === $post_type ) ) {
						$found = true;
					}
					break;
			}
			break;
		case 'categories':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' !== $action && 'give_forms_category' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' === $action && 'give_forms_category' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit-tags.php' && 'give_forms_category' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
		case 'tags':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' !== $action && 'give_forms_tag' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' === $action && 'give_forms_tag' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit-tags.php' && 'give_forms_tag' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
		case 'payments':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-payment-history' === $page && false === $view ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-payment-history' === $page && 'view-order-details' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-payment-history' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'reports':
			switch ( $passed_view ) {
				// If you want to do something like enqueue a script on a particular report's duration, look at $_GET[ 'range' ]
				case 'earnings':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-reports' === $page && ( 'earnings' === $view || '-1' === $view || false === $view ) ) {
						$found = true;
					}
					break;
				case 'donors':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-reports' === $page && 'customers' === $view ) {
						$found = true;
					}
					break;
				case 'gateways':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-reports' === $page && 'gateways' === $view ) {
						$found = true;
					}
					break;
				case 'export':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-reports' === $page && 'export' === $view ) {
						$found = true;
					}
					break;
				case 'logs':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-reports' === $page && 'logs' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-reports' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'settings':
			switch ( $passed_view ) {
				case 'general':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-settings' === $page && ( 'general' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'gateways':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-settings' === $page && 'gateways' === $tab ) {
						$found = true;
					}
					break;
				case 'emails':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-settings' === $page && 'emails' === $tab ) {
						$found = true;
					}
					break;
				case 'display':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-settings' === $page && 'display' === $tab ) {
						$found = true;
					}
					break;
				case 'licenses':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-settings' === $page && 'licenses' === $tab ) {
						$found = true;
					}
					break;
				case 'api':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-settings' === $page && 'api' === $tab ) {
						$found = true;
					}
					break;
				case 'advanced':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-settings' === $page && 'advanced' === $tab ) {
						$found = true;
					}
					break;
				case 'system_info':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-settings' === $page && 'system_info' === $tab ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-settings' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'addons':
			if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-addons' === $page ) {
				$found = true;
			}
			break;
		case 'donors':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-donors' === $page && false === $view ) {
						$found = true;
					}
					break;
				case 'overview':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-donors' === $page && 'overview' === $view ) {
						$found = true;
					}
					break;
				case 'notes':
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-donors' === $page && 'notes' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-donors' === $page ) {
						$found = true;
					}
					break;
			}
			break;
		case 'reports':
			if ( ( 'give_forms' == $typenow || 'give_forms' === $post_type ) && $pagenow == 'edit.php' && 'give-reports' === $page ) {
				$found = true;
			}
			break;
		default:
			global $give_payments_page, $give_settings_page, $give_reports_page, $give_system_info_page, $give_add_ons_page, $give_settings_export, $give_upgrades_screen, $give_customers_page, $give_tools_page;

			$admin_pages = apply_filters( 'give_admin_pages', array(
				$give_payments_page,
				$give_settings_page,
				$give_reports_page,
				$give_system_info_page,
				$give_add_ons_page,
				$give_upgrades_screen,
				$give_settings_export,
				$give_customers_page,
				$give_tools_page,
				'widgets.php'
		) );
			if ( 'give_forms' == $typenow || 'index.php' == $pagenow || 'post-new.php' == $pagenow || 'post.php' == $pagenow ) {
				$found = true;
				if ( 'give-upgrades' === $page ) {
					$found = false;
				}
			} elseif ( in_array( $pagenow, $admin_pages ) ) {
				$found = true;
			}
			break;
	}

	return (bool) apply_filters( 'give_is_admin_page', $found, $page, $view, $passed_page, $passed_view );

}


/**
 * Add setting tab to give-settings page
 *
 * @since  1.8
 * @param  array $settings
 * @return array
 */
function give_settings_page_pages( $settings ) {
	include( 'abstract-admin-settings-page.php' );
	include( 'settings/class-settings-cmb2-backward-compatibility.php' );

	$settings = array(
		// General settings.
		include( 'settings/class-settings-general.php' ),

		// Payment Gateways Settings.
		include( 'settings/class-settings-gateways.php' ),

		// Display settings.
		include( 'settings/class-settings-display.php' ),

		// Emails settings.
		include( 'settings/class-settings-email.php' ),

		// Addons settings.
		include( 'settings/class-settings-addon.php' ),

		// License settings.
		include( 'settings/class-settings-license.php' ),

		// Advanced settinns.
		include( 'settings/class-settings-advanced.php' )
	);

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
	include( 'abstract-admin-settings-page.php' );

	$settings = array(
		// Earnings.
		include( 'reporting/class-settings-earnings.php' ),

		// Forms.
		include( 'reporting/class-settings-forms.php' ),

		// Donors.
		include( 'reporting/class-settings-donors.php' ),

		// Gateways.
		include( 'reporting/class-settings-gateways.php' ),

		// Export.
		include( 'reporting/class-settings-export.php' ),
	);

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
	include( 'abstract-admin-settings-page.php' );

	$settings = array(
		// System Info.
		include( 'tools/class-settings-system-info.php' ),

		// Logs.
		include( 'tools/class-settings-logs.php' ),

		// API.
		include( 'tools/class-settings-api.php' ),

		// Data.
		include( 'tools/class-settings-data.php' ),
	);

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
	return 'system-info';
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