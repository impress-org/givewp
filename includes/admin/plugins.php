<?php
/**
 * Admin Plugins
 *
 * @package     Give
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugins row action links
 *
 * @since 1.4
 *
 * @param array $actions An array of plugin action links.
 *
 * @return array An array of updated action links.
 */
function give_plugin_action_links( $actions ) {
	$new_actions = array(
		'settings' => sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'edit.php?post_type=give_forms&page=give-settings' ),
			__( 'Settings', 'give' )
		),
	);

	return array_merge( $new_actions, $actions );
}

add_filter( 'plugin_action_links_' . GIVE_PLUGIN_BASENAME, 'give_plugin_action_links' );


/**
 * Plugin row meta links
 *
 * @since 1.4
 *
 * @param array  $plugin_meta An array of the plugin's metadata.
 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
 *
 * @return array
 */
function give_plugin_row_meta( $plugin_meta, $plugin_file ) {
	if ( $plugin_file != GIVE_PLUGIN_BASENAME ) {
		return $plugin_meta;
	}

	$new_meta_links = array(
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( add_query_arg( array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-row',
					'utm_campaign' => 'admin',
				), 'https://givewp.com/documentation/' )
			),
			__( 'Documentation', 'give' )
		),
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( add_query_arg( array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-row',
					'utm_campaign' => 'admin',
				), 'https://givewp.com/addons/' )
			),
			__( 'Add-ons', 'give' )
		),
	);

	return array_merge( $plugin_meta, $new_meta_links );
}

add_filter( 'plugin_row_meta', 'give_plugin_row_meta', 10, 2 );


/**
 * Get the Parent Page Title in admin section.
 * Based on get_admin_page_title WordPress Function.
 *
 * @since 1.8.16
 *
 * @global string $title
 * @global array  $menu
 * @global array  $submenu
 * @global string $pagenow
 * @global string $plugin_page
 * @global string $typenow
 *
 * @return string $title Page title
 */
function give_get_admin_page_menu_title() {
	$title = '';
	global $menu, $submenu, $pagenow, $plugin_page, $typenow;
	$hook    = get_plugin_page_hook( $plugin_page, $pagenow );
	$parent  = get_admin_page_parent();
	$parent1 = $parent;
	foreach ( array_keys( $submenu ) as $parent ) {
		foreach ( $submenu[ $parent ] as $submenu_array ) {
			if ( isset( $plugin_page ) &&
			     ( $plugin_page === $submenu_array[2] ) &&
			     (
				     ( $parent === $pagenow ) ||
				     ( $parent === $plugin_page ) ||
				     ( $plugin_page === $hook ) ||
				     ( $pagenow === 'admin.php' && $parent1 !== $submenu_array[2] ) ||
				     ( ! empty( $typenow ) && $parent === $pagenow . '?post_type=' . $typenow )
			     )
			) {
				$title = $submenu_array[0];

				return $submenu_array[0];
			}
			if ( $submenu_array[2] !== $pagenow || isset( $_GET['page'] ) ) { // not the current page
				continue;
			}
			if ( isset( $submenu_array[0] ) ) {
				$title = $submenu_array[0];
			} else {
				$title = $submenu_array[3];
			}
		}
	}

	return $title;
}
