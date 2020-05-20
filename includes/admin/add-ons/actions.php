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

	check_admin_referer( 'give-upload-addon' );

	// Remove version from file name.
	$filename = preg_replace( array( '/\(\d\).zip/', '/(.\d).*[\(\d\)]/' ), '', $_FILES['file']['name'] );
	$filename = basename( trim( $filename ), '.zip' );

	// Bailout if user does not has permission.
	if ( ! current_user_can( 'upload_plugins' ) ) {
		wp_send_json_error( array( 'errorMsg' => __( 'Sorry, you are not allowed to upload add-ons on this site.', 'give' ) ) );
	}

	$access_type = get_filesystem_method();

	if ( 'direct' !== $access_type ) {
		wp_send_json_error(
			array(
				'errorMsg' => sprintf(
					__( 'Sorry, you can not upload plugins because GiveWP does not have direct access to the file system. Please <a href="%1$s" target="_blank">click here</a> to upload the add-on.', 'give' ),
					admin_url( 'plugin-install.php?tab=upload' )
				),
			)
		);
	}

	$file_type = wp_check_filetype( $_FILES['file']['name'], array( 'zip' => 'application/zip' ) );

	if ( empty( $file_type['ext'] ) ) {
		wp_send_json_error( array( 'errorMsg' => __( 'Only zip file type allowed to upload. Please upload a valid add-on file.', 'give' ) ) );
	}

	$give_addons_list   = give_get_plugins();
	$is_addon_installed = array();

	if ( ! empty( $give_addons_list ) ) {
		foreach ( $give_addons_list as $addon => $give_addon ) {
			if ( false !== stripos( $addon, $filename ) ) {
				$is_addon_installed = $give_addon;
			}
		}
	}

	// Bailout  if addon already installed
	if ( ! empty( $is_addon_installed ) ) {
		wp_send_json_error(
			array(
				'errorMsg'   => __( 'This add-on is already installed.', 'give' ),
				'pluginInfo' => $is_addon_installed,
			)
		);
	}

	$upload_status = wp_handle_upload( $_FILES['file'], array( 'test_form' => false ) );

	// Bailout if has any upload error
	if ( empty( $upload_status['file'] ) ) {
		wp_send_json_error( $upload_status );
	}

	// @todo: check how WordPress verify plugin files before uploading to plugin directory

	/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
	$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array() );

	/* initialize the API */
	if ( ! WP_Filesystem( $creds ) ) {
		/* any problems and we exit */
		wp_send_json_error(
			array(
				'errorMsg' => __( 'File system does not load correctly.', 'give' ),
			)
		);
	}

	$unzip_status = unzip_file( $upload_status['file'], $wp_filesystem->wp_plugins_dir() );

	// Remove file.
	@unlink( $upload_status['file'] );

	// Bailout if not able to unzip file successfully
	if ( is_wp_error( $unzip_status ) ) {
		wp_send_json_error(
			array(
				'errorMsg' => $unzip_status,
			)
		);
	}

	// Delete cache and get current installed addon plugin path.
	wp_clean_plugins_cache( true );

	$give_addons_list = give_get_plugins();
	$installed_addon  = array();

	if ( ! empty( $give_addons_list ) ) {
		foreach ( $give_addons_list as $addon => $give_addon ) {
			if ( false !== stripos( $addon, $filename ) ) {
				$installed_addon         = $give_addon;
				$installed_addon['path'] = $addon;
			}
		}
	}

	wp_send_json_success(
		array(
			'pluginPath'         => $installed_addon['path'],
			'pluginName'         => $installed_addon['Name'],
			'nonce'              => wp_create_nonce( "give_activate-{$installed_addon['path']}" ),
			'licenseSectionHtml' => Give_License::render_licenses_list(),
		)
	);
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

	$license_key                  = ! empty( $_POST['license'] ) ? give_clean( $_POST['license'] ) : '';
	$is_activating_single_license = ! empty( $_POST['single'] ) ? absint( $_POST['single'] ) : '';
	$is_reactivating_license      = ! empty( $_POST['reactivate'] ) ? absint( $_POST['reactivate'] ) : '';
	$plugin_slug                  = $is_activating_single_license ? give_clean( $_POST['addon'] ) : '';
	$licenses                     = get_option( 'give_licenses', array() );

	if ( ! $license_key ) {
		wp_send_json_error(
			array(
				'errorMsg' => __( 'Sorry, you entered an invalid key.', 'give' ),
			)
		);

	} elseif (
		! $is_reactivating_license
		&& array_key_exists( $license_key, $licenses )
	) {
		// If admin already activated license but did not install add-on then send license info show notice to admin with download link.
		$license = $licenses[ $license_key ];
		if ( empty( $license['is_all_access_pass'] ) ) {
			$plugin_data = Give_License::get_plugin_by_slug( $license['plugin_slug'] );

			// Plugin license activated but does not install, sent notice which allow admin to download add-on.
			if ( empty( $plugin_data ) ) {
				wp_send_json_success( $license );
			}
		}

		wp_send_json_error(
			array(
				'errorMsg' => __( 'This license key is already in use on this website.', 'give' ),
			)
		);
	}

	// Check license.
	$check_license_res = Give_License::request_license_api(
		array(
			'edd_action' => 'check_license',
			'license'    => $license_key,
		),
		true
	);

	// Make sure there are no errors.
	if ( is_wp_error( $check_license_res ) ) {
		wp_send_json_error(
			array(
				'errorMsg' => $check_license_res->get_error_message(),
			)
		);
	}

	// Check if license valid or not.
	if ( ! $check_license_res['success'] ) {
		wp_send_json_error(
			array(
				'errorMsg' => sprintf(
					__( 'Sorry, this license was unable to activate because the license status returned as <code>%2$s</code>. Please visit your <a href="%1$s" target="_blank">license dashboard</a> to check the details and access priority support.', 'give' ),
					Give_License::get_account_url(),
					$check_license_res['license']
				),
			)
		);
	}

	if (
		$is_activating_single_license
		&& ! empty( $check_license_res['plugin_slug'] )
		&& $plugin_slug !== $check_license_res['plugin_slug']
	) {
		wp_send_json_error(
			array(
				'errorMsg' => sprintf(
					__( 'Sorry, we are unable to activate this license because this key does not belong to this add-on. Please visit your <a href="%1$s" target="_blank">license dashboard</a> to check the details and access priority support.', 'give' ),
					Give_License::get_account_url()
				),
			)
		);
	}

	// Activate license.
	$activate_license_res = Give_License::request_license_api(
		array(
			'edd_action' => 'activate_license',
			'item_name'  => $check_license_res['item_name'],
			'license'    => $license_key,
		),
		true
	);

	if ( is_wp_error( $activate_license_res ) ) {
		wp_send_json_error(
			array(
				'errorMsg' => $check_license_res->get_error_message(),
			)
		);
	}

	// Return error if license activation is not success and admin is not reactivating add-on.
	if ( ! $is_reactivating_license && ! $activate_license_res['success'] ) {

		$response['errorMsg'] = sprintf(
			__( 'Sorry, this license was unable to activate because the license status returned as <code>%2$s</code>. Please visit your <a href="%1$s" target="_blank">license dashboard</a> to check the details and access priority support.', 'give' ),
			Give_License::get_account_url(),
			$check_license_res['license']
		);

		wp_send_json_error( $response );
	}

	$check_license_res['license']          = $activate_license_res['license'];
	$check_license_res['site_count']       = $activate_license_res['site_count'];
	$check_license_res['activations_left'] = $activate_license_res['activations_left'];

	// Remove single license which is part of all access pass or already existed all access pass key because admin can only one active.
	// @see https://github.com/impress-org/givewp/issues/4669
	if ( ! empty( $check_license_res['is_all_access_pass'] ) ) {
		$addonSlugs = Give_License::getAddonSlugsFromAllAccessPassLicense( $check_license_res );
		foreach ( $licenses as $license_key => $data ) {
			if ( in_array( $data['plugin_slug'], $addonSlugs, true ) || ! empty( $data['is_all_access_pass'] ) ) {
				unset( $licenses[ $license_key ] );
			}
		}
	}

	$licenses[ $check_license_res['license_key'] ] = $check_license_res;
	update_option( 'give_licenses', $licenses );

	// Get license section HTML.
	$response         = $check_license_res;
	$response['html'] = $is_activating_single_license && empty( $check_license_res['is_all_access_pass'] )
		? Give_License::html_by_plugin( Give_License::get_plugin_by_slug( $check_license_res['plugin_slug'] ) )
		: Give_License::render_licenses_list();

	// Return error if license activation is not success and admin is reactivating add-on.
	if ( $is_reactivating_license && ! $activate_license_res['success'] ) {

		$response['errorMsg'] = sprintf(
			__( 'Sorry, this license was unable to activate because the license status returned as <code>%2$s</code>. Please visit your <a href="%1$s" target="_blank">license dashboard</a> to check the details and access priority support.', 'give' ),
			Give_License::get_account_url(),
			$check_license_res['license']
		);

		wp_send_json_error( $response );
	}

	// Tell WordPress to look for updates.
	give_refresh_licenses();

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

	// Tell WordPress to look for updates.
	give_refresh_licenses();

	wp_send_json_success(
		array(
			'licenseSectionHtml' => Give_License::render_licenses_list(),
		)
	);
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
		wp_send_json_error(
			array(
				'errorMsg' => __( 'We are unable to deactivate invalid license', 'give' ),
			)
		);
	}

	/* @var array|WP_Error $response */
	$response = Give_License::request_license_api(
		array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => $item_name,
		),
		true
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error(
			array(
				'errorMsg' => $response->get_error_message(),
				'response' => $license,
			)
		);
	}

	$is_all_access_pass = $give_licenses[ $license ]['is_all_access_pass'];

	if ( ! empty( $give_licenses[ $license ] ) ) {
		unset( $give_licenses[ $license ] );
		update_option( 'give_licenses', $give_licenses );
	}

	$response['html'] = $is_all_access_pass
		? Give_License::render_licenses_list()
		: Give_License::html_by_plugin( Give_License::get_plugin_by_slug( $plugin_dirname ) );

	$response['msg'] = __( 'You have successfully deactivated the license.', 'give' );

	// Tell WordPress to look for updates.
	give_refresh_licenses();

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

	$data = Give_License::refresh_license_status();

	// Update date and reset counter.
	if ( $data['compare'] === date( 'Ymd' ) && 5 <= $data['count'] ) {
		wp_send_json_error();
	}

	// Update date and reset counter.
	if ( $data['compare'] < date( 'Ymd' ) ) {
		$data['compare'] = date( 'Ymd' );
		$data['count']   = 0;
	}

	// Update time.
	$data['time'] = time();

	++ $data['count'];

	update_option( 'give_licenses_refreshed_last_checked', $data, 'no' );

	give_refresh_licenses();

	$local_date = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $data['time'] ) ) );
	wp_send_json_success(
		array(
			'html'          => Give_License::render_licenses_list(),
			'refreshButton' => 5 <= $data['count'],
			'refreshStatus' => $data,
			'lastUpdateMsg' => sprintf(
				__( 'Last refreshed on %1$s at %2$s', 'give' ),
				date( give_date_format(), $local_date ),
				date( 'g:i a', $local_date )
			),
		)
	);
}

add_action( 'wp_ajax_give_refresh_all_licenses', 'give_refresh_all_licenses_handler' );


/**
 * Updates information on the "View version x.x details" page with custom data.
 * Note: only for internal use
 *
 * @param mixed  $_data
 * @param string $_action
 * @param object $_args
 *
 * @return object $_data
 * @since 2.5.0
 * @uses  api_request()
 */
function give_plugins_api_filter( $_data, $_action = '', $_args = null ) {
	// Exit.
	if ( 'plugin_information' !== $_action ) {
		return $_data;
	}

	$plugin = Give_License::get_plugin_by_slug( $_args->slug );

	if (
		! $plugin
		|| 'add-on' !== $plugin['Type']
		|| false === strpos( $_args->slug, 'give-' )
	) {
		return $_data;
	}

	$plugin_data = get_site_transient( 'update_plugins' );

	if ( ! $plugin_data ) {
		give_refresh_licenses();
	}

	$plugin_data = ! empty( $plugin_data->response[ $plugin['Path'] ] )
		? $plugin_data->response[ $plugin['Path'] ]
		: array();

	if ( ! $plugin_data ) {
		return $_data;
	}

	$_data = $plugin_data;

	return $_data;
}

add_filter( 'plugins_api', 'give_plugins_api_filter', 9999, 3 );


/**
 * Check add-ons updates when WordPress check plugin updates
 *
 * @since 2.5.0
 */
add_filter( 'pre_set_site_transient_update_plugins', 'give_check_addon_updates', 999, 1 );


/**
 * Show plugin update notification on multi-site
 *
 * @param string $file
 * @param array  $plugin
 *
 * @since 2.5.0
 */
function give_show_update_notification_on_multisite( $file, $plugin ) {
	if ( is_network_admin() ) {
		return;
	}

	if ( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

	if ( ! is_multisite() ) {
		return;
	}

	if (
		! $plugin
		|| empty( $plugin['slug'] )
		|| false === strpos( $plugin['slug'], 'give-' )
	) {
		return;
	}

	$plugin_data = Give_License::get_plugin_by_slug( $plugin['slug'] );

	// Only show notices for Give add-ons
	if ( 'add-on' !== $plugin_data['Type'] ) {
		return;
	}

	// Do not print any message if updates does not exist.
	$update_cache = get_site_transient( 'update_plugins' );

	if ( ! isset( $update_cache->response[ $file ] ) ) {
		return;
	}

	if ( ! empty( $update_cache->response[ $plugin_data['Path'] ] ) && version_compare( $plugin_data['Version'], $plugin['new_version'], '<' ) ) {
		printf(
			'<tr class="plugin-update-tr %3$s" id="%1$s-update" data-slug="%1$s" data-plugin="%2$s">',
			$plugin['slug'],
			$file,
			'active' === $plugin_data['Status'] ? 'active' : 'inactive'
		);

		echo '<td colspan="3" class="plugin-update colspanchange">';
		echo '<div class="update-message notice inline notice-warning notice-alt"><p>';

		$changelog_link = self_admin_url( "plugin-install.php?tab=plugin-information&plugin={$plugin['slug']}&section=changelog&TB_iframe=true&width=772&height=299" );

		if ( empty( $plugin['download_link'] ) ) {
			printf(
				__( 'There is a new version of %1$s available. %2$sView version %3$s details%4$s.', 'give' ),
				esc_html( $plugin_data['Name'] ),
				'<a target="_blank" class="thickbox open-plugin-details-modal" href="' . esc_url( $changelog_link ) . '">',
				esc_html( $plugin['new_version'] ),
				'</a>'
			);
		} else {
			printf(
				__( 'There is a new version of %1$s available. %2$sView version %3$s details%4$s or %5$supdate now%6$s.', 'give' ),
				esc_html( $plugin_data['Name'] ),
				'<a target="_blank" class="thickbox open-plugin-details-modal" href="' . esc_url( $changelog_link ) . '">',
				esc_html( $plugin['new_version'] ),
				'</a>',
				'<a target="_blank" class="update-link" href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file, 'upgrade-plugin_' . $file ) ) . '">',
				'</a>'
			);
		}

		do_action( "in_plugin_update_message-{$file}", $plugin, $plugin );

		echo '</p></div></td></tr>';
	}
}

add_action( 'after_plugin_row', 'give_show_update_notification_on_multisite', 10, 2 );

/**
 * Show plugin update notification on single site
 *
 * @param $file
 * @param $plugin
 *
 * @since 2.5.0
 */
function give_show_update_notification_on_single_site( $file, $plugin ) {
	if ( ! current_user_can( 'update_plugins' ) || is_multisite() ) {
		return;
	}

	if (
		! $plugin
		|| empty( $plugin['slug'] )
		|| false === strpos( $plugin['slug'], 'give-' )
	) {
		return;
	}

	$plugin_data = Give_License::get_plugin_by_slug( $plugin['slug'] );

	// Only show notices for Give add-ons
	if (
		'add-on' !== $plugin_data['Type']
		|| $plugin_data['License']
	) {
		return;
	}

	// Do not print any message if updates does not exist.
	$update_plugins = get_site_transient( 'update_plugins' );
	if ( ! isset( $update_plugins->response[ $file ] ) ) {
		return;
	}

	// Remove core update notice.
	remove_action( "after_plugin_row_{$file}", 'wp_plugin_update_row' );

	$update_notice_wrap = '<tr class="plugin-update-tr %3$s"><td colspan="3" class="colspanchange"><div class="update-message notice inline notice-warning notice-alt give-invalid-license"><p>%1$s %2$s</p></div></td></tr>';
	$changelog_link     = self_admin_url( "plugin-install.php?tab=plugin-information&plugin={$plugin['slug']}&section=changelog&TB_iframe=true&width=772&height=299" );

	echo sprintf(
		$update_notice_wrap,
		sprintf(
			__( 'There is a new version of %1$s available. %2$sView version %3$s details%4$s.', 'give' ),
			esc_html( $plugin_data['Name'] ),
			'<a target="_blank" class="thickbox open-plugin-details-modal" href="' . esc_url( $changelog_link ) . '">',
			esc_html( $plugin['new_version'] ),
			'</a>'
		),
		sprintf(
			'Please <a href="%1$s" target="_blank">activate your license</a> to receive updates and support.',
			esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=licenses' ) )
		),
		'active' === $plugin_data['Status'] ? 'active' : 'inactive'
	);
}

add_action( 'after_plugin_row', 'give_show_update_notification_on_single_site', 1, 2 );

/**
 * Look for Give core add-ons update when someone manually presses the "Check Again" button within WP's Dashboard > Updates
 *
 * @since 2.5.11
 */
function give_refresh_license_on_force_check() {
	if ( isset( $_GET['force-check'] ) ) {
		give_refresh_licenses();
	}
}
add_action( 'load-update-core.php', 'give_refresh_license_on_force_check', 9 );


