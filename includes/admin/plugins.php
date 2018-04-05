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
 * Get the Parent Page Menu Title in admin section.
 * Based on get_admin_page_title WordPress Function.
 *
 * @since 1.8.17
 *
 * @global array  $submenu
 * @global string $plugin_page
 *
 * @return string $title Page title
 */
function give_get_admin_page_menu_title() {
	$title = '';
	global $submenu, $plugin_page;

	foreach ( array_keys( $submenu ) as $parent ) {
		if( 'edit.php?post_type=give_forms' !== $parent ) {
			continue;
		}

		foreach ( $submenu[ $parent ] as $submenu_array ) {
			if( $plugin_page !== $submenu_array[2] ){
				continue;
			}

			$title = isset( $submenu_array[0] ) ?
				$submenu_array[0] :
				$submenu_array[3];
		}
	}

	return $title;
}

/**
 * Store recently activated Give's addons to wp options.
 *
 * @since 2.1.0
 */
function give_recently_activated_addons() {
	// Check if action is set.
	if ( isset( $_REQUEST["action"] ) ) {
		$plugin_action = ( '-1' !== $_REQUEST['action'] ) ? $_REQUEST['action'] : ( isset( $_REQUEST['action2'] ) ? $_REQUEST['action2'] : '' );
		$plugins       = array();

		switch ( $plugin_action ) {
			case 'activate': // Single add-on activation.
				$plugins[] = $_REQUEST["plugin"];
				break;
			case 'activate-selected': // If multiple add-ons activated.
				$plugins = $_REQUEST["checked"];
				break;
		}

		if ( ! empty( $plugins ) ) {
			$give_addons = array();
			foreach ( $plugins as $plugin ) {
				// Get plugins which has 'Give-' as prefix.
				if ( stripos( $plugin, 'Give-' ) !== false ) {
					$give_addons[] = $plugin;
				}
			}

			if ( ! empty( $give_addons ) ) {
				// Update the Give's activated add-ons.
				update_option( 'give_recently_activated_addons', $give_addons );
			}
		}
	}
}

// Add add-on plugins to wp option table.
add_action( 'activated_plugin', 'give_recently_activated_addons', 10 );

/**
 * Create new menu in plugin section that include all the add-on
 *
 * @since 2.1.0
 *
 * @param $plugin_menu
 *
 * @return mixed
 */
function give_filter_addons_do_filter_addons( $plugin_menu ) {
	global $plugins;

	foreach ( $plugins['all'] as $plugin_slug => $plugin_data ) {

		if ( false !== strpos( $plugin_data['Name'], 'Give' ) && false !== strpos( $plugin_data['AuthorName'], 'WordImpress' ) ) {
			$plugins['give'][ $plugin_slug ]           = $plugins['all'][ $plugin_slug ];
			$plugins['give'][ $plugin_slug ]['plugin'] = $plugin_slug;
			// replicate the next step
			if ( current_user_can( 'update_plugins' ) ) {
				$current = get_site_transient( 'update_plugins' );
				if ( isset( $current->response[ $plugin_slug ] ) ) {
					$plugins['give'][ $plugin_slug ]['update'] = true;
				}
			}

		}
	}

	return $plugin_menu;

}

add_filter( 'show_advanced_plugins', 'give_filter_addons_do_filter_addons' );

/**
 * Make the Give Menu as an default menu and update the Menu Name
 *
 * @since 2.1.0
 *
 * @param $views
 *
 * @return mixed
 */
function give_filter_addons_filter_addons( $views ) {

	global $status, $plugins;

	if ( ! empty( $plugins['give'] ) ) {
		$class = "";

		if ( $status == 'give' ) {
			$class = 'current';
		}

		$views['give'] = sprintf(
			'<a class="%s" href="plugins.php?plugin_status=give"> %s <span class="count">(%s) </span></a>',
			$class,
			__( 'Give', 'give' ),
			count( $plugins['give'] )
		);
	}

	return $views;
}

add_filter( 'views_plugins', 'give_filter_addons_filter_addons' );

/**
 * Set the Give as the Main menu when admin click on the Give Menu in Plugin section.
 *
 * @since 2.1.0
 *
 * @param $plugins
 *
 * @return mixed
 */
function give_prepare_filter_addons( $plugins ) {
	global $status;

	if ( isset( $_REQUEST['plugin_status'] ) && $_REQUEST['plugin_status'] === 'give' ) {
		$status = 'give';
	}

	return $plugins;
}

add_filter( 'all_plugins', 'give_prepare_filter_addons' );
