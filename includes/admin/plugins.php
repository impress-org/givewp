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
 * @param array $links already defined action links
 * @param string $file plugin file path and name being processed
 *
 * @return array $links
 */
function give_plugin_action_links( $links, $file ) {
	$settings_link = '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings' ) . '">' . esc_html__( 'Settings', 'give' ) . '</a>';
	if ( $file == 'give/give.php' ) {
		array_unshift( $links, $settings_link );
	}

	return $links;
}

add_filter( 'plugin_action_links', 'give_plugin_action_links', 10, 2 );


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

	$give_addons_link = esc_url( add_query_arg( array(
			'utm_source'   => 'plugins-page',
			'utm_medium'   => 'plugin-row',
			'utm_campaign' => 'admin',
		), 'https://givewp.com/addons/' )
	);

	$give_docs_link = esc_url( add_query_arg( array(
			'utm_source'   => 'plugins-page',
			'utm_medium'   => 'plugin-row',
			'utm_campaign' => 'admin',
		), 'https://givewp.com/documentation/' )
	);

	$links = array(
		'<a href="' . $give_docs_link . '" target="_blank">' . esc_html__( 'Documentation', 'give' ) . '</a>',
		'<a href="' . $give_addons_link . '" target="_blank">' . esc_html__( 'Add-ons', 'give' ) . '</a>',
	);

	$input = array_merge( $input, $links );

	return $input;
}

add_filter( 'plugin_row_meta', 'give_plugin_row_meta', 10, 2 );
