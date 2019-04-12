<?php
/**
 * Admin Add-ons Actions
 *
 * @package     Give
 * @subpackage  Admin/Add-ons/Actions
 * @copyright   Copyright (c) 2019, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax addon upload handler
 *
 * Note: only for internal use
 *
 * @since 2.5.0
 */
function give_upload_addon_handler() {
	/* @var WP_Filesystem_Direct $wp_filesystem */
	global $wp_filesystem;

	$addon_authors = array( 'WordImpress', 'GiveWP' );
	$filename      = basename( $_FILES['file']['name'], '.zip' );

	check_admin_referer( 'give-upload-addon' );

	// Bailout if user does not has permission.
	if ( ! current_user_can( 'upload_plugins' ) ) {
		wp_send_json_error( __( 'Sorry, you are not allowed to upload add-ons on this site.', 'give' ) );
	}

	// Bailout if not upload file or not uploading Give addon
	if ( empty( $_FILES ) || false === stripos( $filename, 'Give' ) ) {
		wp_send_json_error( __( 'Please upload a valid add-on file.', 'give' ) );
	}

	$access_type = get_filesystem_method();

	if ( 'direct' !== $access_type ) {
		wp_send_json_error(
			array(
				'errorMsg' => sprintf(
					__( 'Sorry, you can not upload plugin from here because we do not have direct access to file system. Please <a href="%1$s" target="_blank">click here</a> to upload Give Add-on.', 'give' ),
					admin_url( 'plugin-install.php?tab=upload' )
				),
			)
		);
	}

	$file_type = wp_check_filetype( $_FILES['file']['name'], array( 'zip' => 'application/zip' ) );

	if ( empty( $file_type['ext'] ) ) {
		wp_send_json_error( __( 'Only zip file type allowed to upload. Please upload a valid add-on file.', 'give' ) );
	}

	$give_addons_list   = give_get_plugins();
	$is_addon_installed = array();

	if ( ! empty( $give_addons_list ) ) {
		foreach ( $give_addons_list as $addon => $give_addon ) {
			// Only show Give Core Activated Add-Ons.
			if ( ! in_array( $give_addon['AuthorName'], $addon_authors ) ) {
				continue;
			}

			if ( false !== stripos( $addon, $filename ) ) {
				$is_addon_installed = $give_addon;
			}
		}
	}

	// Bailout  if addon already installed
	if ( ! empty( $is_addon_installed ) ) {
		wp_send_json_error( array(
			'errorMsg'   => __( 'This addon is already installed', 'give' ),
			'pluginInfo' => $is_addon_installed,
		) );
	}

	$upload_status = wp_handle_upload( $_FILES['file'], array( 'test_form' => false ) );

	// Bailout if has any upload error
	if ( empty( $upload_status['file'] ) ) {
		wp_send_json_error( $upload_status );
	}

	// @todo: check how wordpress verify plugin files before uploading to plugin directory

	/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
	$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array() );

	/* initialize the API */
	if ( ! WP_Filesystem( $creds ) ) {
		/* any problems and we exit */
		wp_send_json_error();
	}

	$unzip_status = unzip_file( $upload_status['file'], $wp_filesystem->wp_plugins_dir() );

	// Remove file.
	@unlink( $upload_status['file'] );

	// Bailout if not able to unzip file successfully
	if ( is_wp_error( $unzip_status ) ) {
		wp_send_json_error( $unzip_status );
	}

	// Delete cache and get current installed addon plugin path.
	wp_cache_delete( 'plugins', 'plugins' );
	$give_addons_list = get_plugins();
	$installed_addon  = array();

	if ( ! empty( $give_addons_list ) ) {
		foreach ( $give_addons_list as $addon => $give_addon ) {
			// Only show Give Core Activated Add-Ons.
			if ( ! in_array( $give_addon['AuthorName'], $addon_authors ) ) {
				continue;
			}

			if ( false !== stripos( $addon, $filename ) ) {
				$installed_addon         = $give_addon;
				$installed_addon['path'] = $addon;
			}
		}
	}

	wp_send_json_success( array(
		'pluginPath' => $installed_addon['path'],
		'pluginName' => $installed_addon['Name'],
		'nonce'      => wp_create_nonce( "give_activate-{$installed_addon['path']}" ),
	) );
}

add_action( 'wp_ajax_give_upload_addon', 'give_upload_addon_handler' );

/**
 * Ajax license inquiry handler
 *
 * Note: only for internal use
 *
 * @since 2.5.0
 */
function give_get_license_info_handler() {
	$license = give_clean( $_POST['license'] );

	if ( ! $license ) {
		wp_send_json_error(
			array(
				'errorMsg' => __( 'Sorry, you entered a invalid key.', 'give' ),
			)
		);
	}

	check_admin_referer( 'give-license-activator-nonce' );

	// Data to send to the API.
	$api_params = array(
		'edd_action' => 'check_license', // never change from "edd_" to "give_"!
		'license'    => $license,
		'url'        => home_url(),
	);

	// Call the API.
	$response = wp_remote_post(
		'http://staging.givewp.com/chechout', // @todo convert this url to live site.
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

	// Make sure there are no errors.
	if ( is_wp_error( $response ) ) {
		wp_send_json_error(
			array(
				'errorMsg' => $response->get_error_message(),
			)
		);
	}

	$response = json_decode( wp_remote_retrieve_body( $response ) );

	wp_send_json_success( $response );
}

add_action( 'wp_ajax_give_get_license_info', 'give_get_license_info_handler' );


/**
 * Activate addon handler
 *
 * Note: only for internal use
 *
 * @since 2.5.0
 */
function give_activate_addon_handler() {
	$plugin_path = give_clean( $_POST['plugin'] );

	check_admin_referer( "give_activate-{$plugin_path}" );

	$status = activate_plugin( $plugin_path );

	if ( is_wp_error( $status ) ) {
		wp_send_json_error( array( 'errorMsg' => $status->get_error_message() ) );
	}

	wp_send_json_success( $status );
}

add_action( 'wp_ajax_give_activate_addon', 'give_activate_addon_handler' );
