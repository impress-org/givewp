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

	// Tell WordPress to look for updates.
	set_site_transient( 'update_plugins', null );

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
	check_admin_referer( 'give-license-activator-nonce' );

	// check user permission.
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		give_die();
	}

	$license_key                  = give_clean( $_POST['license'] );
	$is_activating_single_license = isset( $_POST['single'] ) ? ! ! absint( $_POST['single'] ) : '';
	$licenses                     = get_option( 'give_licenses', array() );


	if ( ! $license_key ) {
		wp_send_json_error( array(
			'errorMsg' => __( 'Sorry, you entered a invalid key.', 'give' ),
		) );
	} else if ( array_key_exists( $license_key, $licenses ) ) {
		wp_send_json_error( array(
			'errorMsg' => __( 'Sorry, this license key is already in use on this website.', 'give' ),
		) );
	}


	// Check license.
	$check_license_res = Give_License::request_license_api( array(
		'edd_action' => 'check_license',
		'license'    => $license_key,
	), true );

	// Make sure there are no errors.
	if ( is_wp_error( $check_license_res ) ) {
		wp_send_json_error( array(
			'errorMsg' => $check_license_res->get_error_message(),
		) );
	}

	// Check if license valid or not.
	if ( ! $check_license_res['success'] ) {
		wp_send_json_error( array(
			'errorMsg' => sprintf(
				__( 'Sorry, we are unable to activate this license because license status is <code>%2$s</code>. Please <a href="%1$s" target="_blank">Visit your dashboard</a> to check this license details.' ),
				'http://staging.givewp.com/my-account/',
				$check_license_res['license']
			),
		) );
	}

	// Activate license.
	$activate_license_res = Give_License::request_license_api( array(
		'edd_action' => 'activate_license',
		'item_name'  => $check_license_res['item_name'],
		'license'    => $license_key,
	), true );

	if ( is_wp_error( $activate_license_res ) ) {
		wp_send_json_error( array(
			'errorMsg' => $check_license_res->get_error_message(),
		) );
	}

	// Check if license activated or not.
	if ( ! $activate_license_res['success'] ) {
		wp_send_json_error( array(
			'errorMsg' => sprintf(
				__( 'Sorry, we are unable to activate this license because license status is <code>%2$s</code>. Please <a href="%1$s" target="_blank">Visit your dashboard</a> to check this license details.' ),
				'http://staging.givewp.com/my-account/',
				$activate_license_res['license']
			),
		) );
	}

	$check_license_res['site_count']       = $activate_license_res['site_count'];
	$check_license_res['activations_left'] = $activate_license_res['activations_left'];

	$licenses[ $check_license_res['license_key'] ] = $check_license_res;
	update_option( 'give_licenses', $licenses );

	// Get license section HTML.
	$response         = $check_license_res;
	$response['html'] = $is_activating_single_license
		? Give_Addons::html_by_plugin( Give_License::get_plugin_by_slug( $check_license_res['plugin_slug'] ) )
		: Give_Addons::render_license_section();

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

	// check user permission.
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		give_die();
	}

	$status = activate_plugin( $plugin_path );

	if ( is_wp_error( $status ) ) {
		wp_send_json_error( array( 'errorMsg' => $status->get_error_message() ) );
	}

	wp_send_json_success( $status );
}

add_action( 'wp_ajax_give_activate_addon', 'give_activate_addon_handler' );


/**
 * deactivate addon handler
 *
 * Note: only for internal use
 *
 * @since 2.5.0
 */
function give_deactivate_license_handler() {
	$license        = give_clean( $_POST['license'] );
	$item_name      = give_clean( $_POST['item_name'] );
	$plugin_dirname = give_clean( $_POST['plugin_dirname'] );

	if ( ! $license || ! $item_name ) {
		wp_send_json_error();
	}

	check_admin_referer( "give-deactivate-license-{$item_name}" );

	// check user permission.
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		give_die();
	}

	$give_licenses = get_option( 'give_licenses', array() );

	if ( empty( $give_licenses[ $license ] ) ) {
		wp_send_json_error( array(
				'errorMsg' => __( 'We are unable to deactivate invalid license', 'give' ),
			)
		);
	}

	/* @var array|WP_Error $response */
	$response = Give_License::request_license_api( array(
		'edd_action' => 'deactivate_license',
		'license'    => $license,
		'item_name'  => $item_name,
	), true );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array(
			'errorMsg' => $response->get_error_message(),
			'response' => $license,
		) );
	}

	// Check if license deactivated or not.
	if ( ! $response['success'] ) {
		wp_send_json_error( array(
			'errorMsg' => sprintf(
				__( 'This license has been deactivated on this site but we are unable to deactivate this on <code>givewp.com</code> because license status is <code>%2$s</code>. Please <a href="%1$s" target="_blank">Visit your dashboard</a> to check this license details.' ),
				'http://staging.givewp.com/my-account/',
				$response['license']
			),
		) );
	}

	$is_all_access_pass = $give_licenses[ $license ]['is_all_access_pass'];

	if ( ! empty( $give_licenses[ $license ] ) ) {
		unset( $give_licenses[ $license ] );
		update_option( 'give_licenses', $give_licenses );
	}

	$response['html'] = $is_all_access_pass
		? Give_Addons::render_license_section()
		: Give_Addons::html_by_plugin( Give_License::get_plugin_by_slug( $plugin_dirname ) );

	// Tell WordPress to look for updates.
	set_site_transient( 'update_plugins', null );

	wp_send_json_success( $response );
}

add_action( 'wp_ajax_give_deactivate_license', 'give_deactivate_license_handler' );


/**
 * Refresh all addons licenses handler
 *
 * Note: only for internal use
 *
 * @since 2.5.0
 */
function give_refresh_all_licenses_handler() {
	check_admin_referer( 'give-refresh-all-licenses' );

	// check user permission.
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		give_die();
	}

	give_refresh_licenses();

	wp_send_json_success( array( 'html' => Give_Addons::render_license_section() ) );
}

add_action( 'wp_ajax_give_refresh_all_licenses', 'give_refresh_all_licenses_handler' );
