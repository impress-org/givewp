<?php
/**
 * Admin Plugins
 *
 * @package     Give
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
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
			esc_html__( 'Settings', 'give' )
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
 * @param array $input already defined meta links
 * @param string $file plugin file path and name being processed
 *
 * @return array $input
 */
function give_plugin_row_meta( $input, $file ) {
	if ( $file != 'give/give.php' ) {
		return $input;
	}

	$give_docs_link = esc_url( add_query_arg( array(
			'utm_source'   => 'plugins-page',
			'utm_medium'   => 'plugin-row',
			'utm_campaign' => 'admin',
		), 'https://givewp.com/documentation/' )
	);

	$give_addons_link = esc_url( add_query_arg( array(
			'utm_source'   => 'plugins-page',
			'utm_medium'   => 'plugin-row',
			'utm_campaign' => 'admin',
		), 'https://givewp.com/addons/' )
	);

	$links = array(
		'<a href="' . $give_docs_link . '" target="_blank">' . esc_html__( 'Documentation', 'give' ) . '</a>',
		'<a href="' . $give_addons_link . '" target="_blank">' . esc_html__( 'Add-ons', 'give' ) . '</a>',
	);

	$input = array_merge( $input, $links );

	return $input;
}

add_filter( 'plugin_row_meta', 'give_plugin_row_meta', 10, 2 );
