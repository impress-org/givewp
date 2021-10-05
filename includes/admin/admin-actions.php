<?php

use Give\Framework\Database\DB;
use Give\Log\ValueObjects\LogType;

/**
 * Admin Actions
 *
 * @package     Give
 * @since       1.0
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @subpackage  Admin/Actions
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load wp editor by ajax.
 *
 * @since 1.8
 */
function give_load_wp_editor() {
	if ( ! isset( $_POST['wp_editor'] ) || ! current_user_can( 'edit_give_forms' ) ) {
		die();
	}

	$wp_editor                     = json_decode( base64_decode( $_POST['wp_editor'] ), true );
	$wp_editor[2]['textarea_name'] = give_clean( $_POST['textarea_name'] );

	wp_editor( wp_kses_post( $wp_editor[0] ), give_clean( $_POST['wp_editor_id'] ), $wp_editor[2] );

	die();
}

add_action( 'wp_ajax_give_load_wp_editor', 'give_load_wp_editor' );


/**
 * Redirect admin to clean url give admin pages.
 *
 * @since 1.8
 * @return bool
 */
function give_redirect_to_clean_url_admin_pages() {
	// Give admin pages.
	$give_pages = [
		'give-payment-history',
		'give-donors',
		'give-reports',
		'give-tools',
	];

	// Get current page.
	$current_page = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : '';

	// Bailout.
	if (
		empty( $current_page )
		|| empty( $_GET['_wp_http_referer'] )
		|| ! in_array( $current_page, $give_pages )
	) {
		return false;
	}

	/**
	 * Verify current page request.
	 *
	 * @since 1.8
	 */
	$redirect = apply_filters( "give_validate_{$current_page}", true );

	if ( $redirect ) {
		// Redirect.
		wp_redirect(
			remove_query_arg(
				[ '_wp_http_referer', '_wpnonce' ],
				wp_unslash( $_SERVER['REQUEST_URI'] )
			)
		);
		exit;
	}
}

add_action( 'admin_init', 'give_redirect_to_clean_url_admin_pages' );


/**
 * Hide Outdated PHP Notice Shortly.
 *
 * This code is used with AJAX call to hide outdated PHP notice for a short period of time
 *
 * @since 1.8.9
 * @return void
 */
function give_hide_outdated_php_notice() {

	if ( ! isset( $_POST['_give_hide_outdated_php_notices_shortly'] ) || ! current_user_can( 'manage_give_settings' ) ) {
		give_die();
	}

	// Transient key name.
	$transient_key = '_give_hide_outdated_php_notices_shortly';

	if ( Give_Cache::get( $transient_key, true ) ) {
		return;
	}

	// Hide notice for 24 hours.
	Give_Cache::set( $transient_key, true, DAY_IN_SECONDS, true );

	give_die();

}

add_action( 'wp_ajax_give_hide_outdated_php_notice', 'give_hide_outdated_php_notice' );

/**
 * Register admin notices.
 *
 * @since 1.8.9
 */
function _give_register_admin_notices() {
	// Bailout.
	if ( ! is_admin() ) {
		return;
	}

	// Bulk action notices.
	if (
		isset( $_GET['action'] ) &&
		! empty( $_GET['action'] )
	) {

		// Add payment bulk notice.
		if (
			current_user_can( 'edit_give_payments' ) &&
			isset( $_GET['payment'] ) &&
			! empty( $_GET['payment'] )
		) {
			$payment_count = isset( $_GET['payment'] ) ? count( $_GET['payment'] ) : 0;

			switch ( $_GET['action'] ) {
				case 'delete':
					Give()->notices->register_notice(
						[
							'id'          => 'bulk_action_delete',
							'type'        => 'updated',
							'description' => sprintf(
								_n(
									'Successfully deleted one donation.',
									'Successfully deleted %d donations.',
									$payment_count,
									'give'
								),
								$payment_count
							),
							'show'        => true,
						]
					);

					break;

				case 'resend-receipt':
					Give()->notices->register_notice(
						[
							'id'          => 'bulk_action_resend_receipt',
							'type'        => 'updated',
							'description' => sprintf(
								_n(
									'Successfully sent email receipt to one recipient.',
									'Successfully sent email receipts to %d recipients.',
									$payment_count,
									'give'
								),
								$payment_count
							),
							'show'        => true,
						]
					);
					break;

				case 'set-status-publish':
				case 'set-status-pending':
				case 'set-status-processing':
				case 'set-status-refunded':
				case 'set-status-revoked':
				case 'set-status-failed':
				case 'set-status-cancelled':
				case 'set-status-abandoned':
				case 'set-status-preapproval':
					Give()->notices->register_notice(
						[
							'id'          => 'bulk_action_status_change',
							'type'        => 'updated',
							'description' => _n(
								'Donation status updated successfully.',
								'Donation statuses updated successfully.',
								$payment_count,
								'give'
							),
							'show'        => true,
						]
					);
					break;
			}// End switch().
		}// End if().
	}// End if().

	// Add give message notices.
	$message_notices = give_get_admin_messages_key();
	if ( ! empty( $message_notices ) ) {
		foreach ( $message_notices as $message_notice ) {
			// Donation reports errors.
			if ( current_user_can( 'view_give_reports' ) ) {
				switch ( $message_notice ) {
					case 'donation-deleted':
						Give()->notices->register_notice(
							[
								'id'          => 'give-donation-deleted',
								'type'        => 'updated',
								'description' => __( 'The donation has been deleted.', 'give' ),
								'show'        => true,
							]
						);
						break;
					case 'email-sent':
						Give()->notices->register_notice(
							[
								'id'          => 'give-email-sent',
								'type'        => 'updated',
								'description' => __( 'The donation receipt has been resent.', 'give' ),
								'show'        => true,
							]
						);
						break;
					case 'refreshed-reports':
						Give()->notices->register_notice(
							[
								'id'          => 'give-refreshed-reports',
								'type'        => 'updated',
								'description' => __( 'The reports cache has been cleared.', 'give' ),
								'show'        => true,
							]
						);
						break;
					case 'donation-note-deleted':
						Give()->notices->register_notice(
							[
								'id'          => 'give-donation-note-deleted',
								'type'        => 'updated',
								'description' => __( 'The donation note has been deleted.', 'give' ),
								'show'        => true,
							]
						);
						break;
				}// End switch().
			}// End if().

			// Give settings notices and errors.
			if ( current_user_can( 'manage_give_settings' ) ) {
				switch ( $message_notice ) {
					case 'settings-imported':
						Give()->notices->register_notice(
							[
								'id'          => 'give-settings-imported',
								'type'        => 'updated',
								'description' => __( 'The settings have been imported.', 'give' ),
								'show'        => true,
							]
						);
						break;
					case 'api-key-generated':
						Give()->notices->register_notice(
							[
								'id'          => 'give-api-key-generated',
								'type'        => 'updated',
								'description' => __( 'API keys have been generated.', 'give' ),
								'show'        => true,
							]
						);
						break;
					case 'api-key-exists':
						Give()->notices->register_notice(
							[
								'id'          => 'give-api-key-exists',
								'type'        => 'updated',
								'description' => __( 'The specified user already has API keys.', 'give' ),
								'show'        => true,
							]
						);
						break;
					case 'api-key-regenerated':
						Give()->notices->register_notice(
							[
								'id'          => 'give-api-key-regenerated',
								'type'        => 'updated',
								'description' => __( 'API keys have been regenerated.', 'give' ),
								'show'        => true,
							]
						);
						break;
					case 'api-key-revoked':
						Give()->notices->register_notice(
							[
								'id'          => 'give-api-key-revoked',
								'type'        => 'updated',
								'description' => __( 'API keys have been revoked.', 'give' ),
								'show'        => true,
							]
						);
						break;
					case 'sent-test-email':
						Give()->notices->register_notice(
							[
								'id'          => 'give-sent-test-email',
								'type'        => 'updated',
								'description' => sprintf( __( 'The test email has been sent to %s.', 'give' ), wp_get_current_user()->user_email ),
								'show'        => true,
							]
						);
						break;
					case 'matched-success-failure-page':
						Give()->notices->register_notice(
							[
								'id'          => 'give-matched-success-failure-page',
								'type'        => 'updated',
								'description' => __( 'You cannot set the success and failed pages to the same page', 'give' ),
								'show'        => true,
							]
						);
						break;
					case 'akismet-deblacklisted-email':
						Give()->notices->register_notice(
							[
								'id'          => 'give-akismet-deblacklisted-email',
								'type'        => 'updated',
								'description' => __( 'Email de-blacklisted successfully. Now Donor will able to process donation with email flagged as spam', 'give' ),
								'show'        => true,
								'dismissible' => 'auto',
							]
						);
						break;
				}// End switch().
			}// End if().

			// Payments errors.
			if ( current_user_can( 'edit_give_payments' ) ) {
				switch ( $message_notice ) {
					case 'note-added':
						Give()->notices->register_notice(
							[
								'id'          => 'give-note-added',
								'type'        => 'updated',
								'description' => __( 'The donation note has been added.', 'give' ),
								'show'        => true,
							]
						);
						break;
					case 'payment-updated':
						Give()->notices->register_notice(
							[
								'id'          => 'give-payment-updated',
								'type'        => 'updated',
								'description' => __( 'The donation has been updated.', 'give' ),
								'show'        => true,
							]
						);
						break;
				}// End switch().
			}// End if().

			// Donor Notices.
			if ( current_user_can( 'edit_give_payments' ) ) {
				switch ( $message_notice ) {
					case 'donor-deleted':
						Give()->notices->register_notice(
							[
								'id'          => 'give-donor-deleted',
								'type'        => 'updated',
								'description' => __( 'The selected donor(s) has been deleted.', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'donor-donations-deleted':
						Give()->notices->register_notice(
							[
								'id'          => 'give-donor-donations-deleted',
								'type'        => 'updated',
								'description' => __( 'The selected donor(s) and the associated donation(s) has been deleted.', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'confirm-delete-donor':
						Give()->notices->register_notice(
							[
								'id'          => 'give-confirm-delete-donor',
								'type'        => 'updated',
								'description' => __( 'You must confirm to delete the selected donor(s).', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'invalid-donor-id':
						Give()->notices->register_notice(
							[
								'id'          => 'give-invalid-donor-id',
								'type'        => 'updated',
								'description' => __( 'Invalid Donor ID.', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'donor-delete-failed':
						Give()->notices->register_notice(
							[
								'id'          => 'give-donor-delete-failed',
								'type'        => 'error',
								'description' => __( 'Unable to delete selected donor(s).', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'email-added':
						Give()->notices->register_notice(
							[
								'id'          => 'give-email-added',
								'type'        => 'updated',
								'description' => __( 'Donor email added.', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'email-removed':
						Give()->notices->register_notice(
							[
								'id'          => 'give-email-removed',
								'type'        => 'updated',
								'description' => __( 'Donor email removed.', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'email-remove-failed':
						Give()->notices->register_notice(
							[
								'id'          => 'give-email-remove-failed',
								'type'        => 'updated',
								'description' => __( 'Failed to remove donor email.', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'primary-email-updated':
						Give()->notices->register_notice(
							[
								'id'          => 'give-primary-email-updated',
								'type'        => 'updated',
								'description' => __( 'Primary email updated for donor.', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'primary-email-failed':
						Give()->notices->register_notice(
							[
								'id'          => 'give-primary-email-failed',
								'type'        => 'updated',
								'description' => __( 'Failed to set primary email.', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'reconnect-user':
						Give()->notices->register_notice(
							[
								'id'          => 'give-reconnect-user',
								'type'        => 'updated',
								'description' => __( 'User has been successfully connected with Donor.', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'disconnect-user':
						Give()->notices->register_notice(
							[
								'id'          => 'give-disconnect-user',
								'type'        => 'updated',
								'description' => __( 'User has been successfully disconnected from donor.', 'give' ),
								'show'        => true,
							]
						);
						break;

					case 'profile-updated':
						Give()->notices->register_notice(
							[
								'id'          => 'give-profile-updated',
								'type'        => 'updated',
								'description' => __( 'Donor information updated successfully.', 'give' ),
								'show'        => true,
							]
						);
						break;
				}// End switch().
			}// End if().
		}
	}

	/**
	 * Spam log admin notice
	 */
	if (
		current_user_can( 'manage_give_settings' ) &&
		give_is_setting_enabled( give_get_option( 'akismet_spam_protection' ) )
	) {
		global $wpdb;

		$current_time               = current_time( 'timestamp' );
		$end_of_current_time_in_gmt = get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( 'tomorrow', $current_time ) ), 'U' );
		$current_time_gmt           = get_gmt_from_date( date( 'Y-m-d H:i:s', $current_time ), 'U' );

		$spam_count = DB::get_var(
			DB::prepare( "SELECT COUNT(id) FROM {$wpdb->give_log} WHERE log_type = %s AND date >= CURDATE();", LogType::SPAM )
		);

		if ( $spam_count && ! Give_Admin_Settings::is_setting_page( 'logs', 'spam' ) ) {
			Give()->notices->register_notice(
				[
					'id'                    => 'give-new-akismet-spam-found',
					'type'                  => 'warning',
					'description'           => sprintf(
						__( 'Akismet flagged %1$s %2$s as spam. If you believe %7$s %5$s actual %6$s, you can whitelist %7$s to allow the %6$s to process donations. <a href="%3$s" title="%4$s">Click here</a> to review spam logs.', 'give' ),
						$spam_count,
						_n( 'donor email', 'donor emails', $spam_count, 'give' ),
						esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-tools&tab=logs&section=spam' ) ),
						__( 'Go to spam log list page', 'give' ),
						_n( 'was', 'were', $spam_count, 'give' ),
						_n( 'donor', 'donors', $spam_count, 'give' ),
						_n( 'this', 'these', $spam_count, 'give' )
					),
					'dismissible_type'      => 'user',
					'dismiss_interval'      => 'custom',
					'dismiss_interval_time' => $end_of_current_time_in_gmt - $current_time_gmt,
				]
			);
		}
	}
}

add_action( 'admin_notices', '_give_register_admin_notices', - 1 );


/**
 * Display admin bar when active.
 *
 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
 *
 * @return bool
 */
function _give_show_test_mode_notice_in_admin_bar( $wp_admin_bar ) {
	$is_test_mode = ! empty( $_POST['test_mode'] ) ?
		give_is_setting_enabled( $_POST['test_mode'] ) :
		give_is_test_mode();

	if (
		! current_user_can( 'view_give_reports' ) ||
		! $is_test_mode
	) {
		return false;
	}

	// Add the main site admin menu item.
	$wp_admin_bar->add_menu(
		[
			'id'     => 'give-test-notice',
			'href'   => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways' ),
			'parent' => 'top-secondary',
			'title'  => __( 'GiveWP Test Mode Active', 'give' ),
			'meta'   => [
				'class' => 'give-test-mode-active',
			],
		]
	);

	return true;
}

add_action( 'admin_bar_menu', '_give_show_test_mode_notice_in_admin_bar', 1000, 1 );

/**
 * Outputs the Give admin bar CSS.
 */
function _give_test_mode_notice_admin_bar_css() {
	if ( ! give_is_test_mode() ) {
		return;
	}
	?>
	<style>
		#wpadminbar .give-test-mode-active > .ab-item {
			color: #fff;
			background-color: #ffba00;
		}

		#wpadminbar .give-test-mode-active:hover > .ab-item, #wpadminbar .give-test-mode-active:hover > .ab-item {
			background-color: rgba(203, 144, 0, 1) !important;
			color: #fff !important;
		}
	</style>
	<?php
}

add_action( 'admin_head', '_give_test_mode_notice_admin_bar_css' );


/**
 * Add Link to Import page in from donation archive and donation single page
 *
 * @since 1.8.13
 */
function give_import_page_link_callback() {
	?>
	<a href="<?php echo esc_url( give_import_page_url() ); ?>"
	   class="page-import-action page-title-action"><?php _e( 'Import Donations', 'give' ); ?></a>

	<?php
	// Check if view donation single page only.
	if ( ! empty( $_REQUEST['view'] ) && 'view-payment-details' === (string) give_clean( $_REQUEST['view'] ) && 'give-payment-history' === give_clean( $_REQUEST['page'] ) ) {
		?>
		<style type="text/css">
			.wrap #transaction-details-heading {
				display: inline-block;
			}
		</style>
		<?php
	}
}

add_action( 'give_payments_page_top', 'give_import_page_link_callback', 11 );

/**
 * Load donation import ajax callback
 * Fire when importing from CSV start
 *
 * @since  1.8.13
 */
function give_donation_import_callback() {
	// Bailout.
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		give_die();
	}

	// Disable Give cache
	Give_Cache::get_instance()->disable();

	$import_setting = [];
	$fields         = isset( $_POST['fields'] ) ? $_POST['fields'] : null;

	parse_str( $fields, $output );

	$import_setting['create_user'] = $output['create_user'];
	$import_setting['mode']        = $output['mode'];
	$import_setting['delimiter']   = $output['delimiter'];
	$import_setting['csv']         = $output['csv'];
	$import_setting['delete_csv']  = $output['delete_csv'];
	$import_setting['dry_run']     = $output['dry_run'];

	// Parent key id.
	$main_key = maybe_unserialize( $output['main_key'] );

	$current    = absint( $_REQUEST['current'] );
	$total_ajax = absint( $_REQUEST['total_ajax'] );
	$start      = absint( $_REQUEST['start'] );
	$end        = absint( $_REQUEST['end'] );
	$next       = absint( $_REQUEST['next'] );
	$total      = absint( $_REQUEST['total'] );
	$per_page   = absint( $_REQUEST['per_page'] );
	if ( empty( $output['delimiter'] ) ) {
		$delimiter = ',';
	} else {
		$delimiter = $output['delimiter'];
	}

	// Processing done here.
	$raw_data                  = give_get_donation_data_from_csv( $output['csv'], $start, $end, $delimiter );
	$raw_key                   = maybe_unserialize( $output['mapto'] );
	$import_setting['raw_key'] = $raw_key;

	if ( ! empty( $output['dry_run'] ) ) {
		$import_setting['csv_raw_data'] = give_get_donation_data_from_csv( $output['csv'], 1, $end, $delimiter );

		$import_setting['donors_list'] = Give()->donors->get_donors(
			[
				'number' => - 1,
				'fields' => [ 'id', 'user_id', 'email' ],
			]
		);
	}

	// Prevent normal emails.
	remove_action( 'give_complete_donation', 'give_trigger_donation_receipt', 999 );
	remove_action( 'give_insert_user', 'give_new_user_notification', 10 );
	remove_action( 'give_insert_payment', 'give_payment_save_page_data' );

	$current_key = $start;
	foreach ( $raw_data as $row_data ) {
		$import_setting['donation_key'] = $current_key;
		give_save_import_donation_to_db( $raw_key, $row_data, $main_key, $import_setting );
		$current_key ++;
	}

	// Check if function exists or not.
	if ( function_exists( 'give_payment_save_page_data' ) ) {
		add_action( 'give_insert_payment', 'give_payment_save_page_data' );
	}
	add_action( 'give_insert_user', 'give_new_user_notification', 10, 2 );
	add_action( 'give_complete_donation', 'give_trigger_donation_receipt', 999 );

	if ( $next == false ) {
		$json_data = [
			'success' => true,
			'message' => __( 'All donation uploaded successfully!', 'give' ),
		];
	} else {
		$index_start = $start;
		$index_end   = $end;
		$last        = false;
		$next        = true;
		if ( $next ) {
			$index_start = $index_start + $per_page;
			$index_end   = $per_page + ( $index_start - 1 );
		}
		if ( $index_end >= $total ) {
			$index_end = $total;
			$last      = true;
		}
		$json_data = [
			'raw_data' => $raw_data,
			'raw_key'  => $raw_key,
			'next'     => $next,
			'start'    => $index_start,
			'end'      => $index_end,
			'last'     => $last,
		];
	}

	$url              = give_import_page_url(
		[
			'step'          => '4',
			'importer-type' => 'import_donations',
			'csv'           => $output['csv'],
			'total'         => $total,
			'delete_csv'    => $import_setting['delete_csv'],
			'success'       => ( isset( $json_data['success'] ) ? $json_data['success'] : '' ),
			'dry_run'       => $output['dry_run'],
		]
	);
	$json_data['url'] = $url;

	$current ++;
	$json_data['current'] = $current;

	$percentage              = ( 100 / ( $total_ajax + 1 ) ) * $current;
	$json_data['percentage'] = $percentage;

	// Enable Give cache
	Give_Cache::get_instance()->enable();

	$json_data = apply_filters( 'give_import_ajax_responces', $json_data, $fields );
	wp_die( json_encode( $json_data ) );
}

add_action( 'wp_ajax_give_donation_import', 'give_donation_import_callback' );

/**
 * Load core settings import ajax callback
 * Fire when importing from JSON start
 *
 * @since  1.8.17
 */

function give_core_settings_import_callback() {
	// Bailout.
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		give_die();
	}

	$fields = isset( $_POST['fields'] ) ? $_POST['fields'] : null;
	parse_str( $fields, $fields );

	$json_data['success'] = false;

	/**
	 * Filter to Modify fields that are being pass by the ajax before importing of the core setting start.
	 *
	 * @access public
	 *
	 * @since  1.8.17
	 *
	 * @param array $fields
	 *
	 * @return array $fields
	 */
	$fields = (array) apply_filters( 'give_import_core_settings_fields', $fields );

	$file_name = ( ! empty( $fields['file_name'] ) ? give_clean( $fields['file_name'] ) : false );

	if ( ! empty( $file_name ) ) {
		$type = ( ! empty( $fields['type'] ) ? give_clean( $fields['type'] ) : 'merge' );

		// Get the json data from the file and then alter it in array format
		$json_string   = give_get_core_settings_json( $file_name );
		$json_to_array = json_decode( $json_string, true );

		// get the current setting from the options table.
		$host_give_options = Give_Cache_Setting::get_settings();

		// Save old settins for backup.
		update_option( 'give_settings_old', $host_give_options, false );

		/**
		 * Filter to Modify Core Settings that are being going to get import in options table as give settings.
		 *
		 * @access public
		 *
		 * @since  1.8.17
		 *
		 * @param array $type Type of Import
		 * @param array $host_give_options Setting old setting that used to be in the options table.
		 * @param array $fields Data that is being send from the ajax
		 *
		 * @param array $json_to_array Setting that are being going to get imported
		 *
		 * @return array $json_to_array Setting that are being going to get imported
		 */
		$json_to_array = (array) apply_filters( 'give_import_core_settings_data', $json_to_array, $type, $host_give_options, $fields );

		update_option( 'give_settings', $json_to_array, false );

		$json_data['success'] = true;
	}

	$json_data['percentage'] = 100;

	/**
	 * Filter to Modify core import setting page url
	 *
	 * @access public
	 *
	 * @since  1.8.17
	 * @return array $url
	 */
	$json_data['url'] = give_import_page_url(
		(array) apply_filters(
			'give_import_core_settings_success_url',
			[
				'step'          => ( empty( $json_data['success'] ) ? '1' : '3' ),
				'importer-type' => 'import_core_setting',
				'success'       => ( empty( $json_data['success'] ) ? '0' : '1' ),
			]
		)
	);

	wp_send_json( $json_data );
}

add_action( 'wp_ajax_give_core_settings_import', 'give_core_settings_import_callback' );

/**
 * Initializes blank slate content if a list table is empty.
 *
 * @since 1.8.13
 */
function give_blank_slate() {
	$blank_slate = new Give_Blank_Slate();
	$blank_slate->init();
}

add_action( 'current_screen', 'give_blank_slate' );

/**
 * Validate Fields of User Profile
 *
 * @since 2.0
 *
 * @param int|bool $update True or False.
 * @param object   $user WP User Data.
 *
 * @param object   $errors Object of WP Errors.
 *
 * @return mixed
 */
function give_validate_user_profile( $errors, $update, $user ) {

	if ( ! empty( $_POST['action'] ) && ( 'adduser' === $_POST['action'] || 'createuser' === $_POST['action'] ) ) {
		return;
	}

	if ( ! empty( $user->ID ) ) {
		$donor = Give()->donors->get_donor_by( 'user_id', $user->ID );

		if ( $donor ) {
			// If Donor is attached with User, then validate first name.
			if ( empty( $_POST['first_name'] ) ) {
				$errors->add(
					'empty_first_name',
					sprintf(
						'<strong>%1$s:</strong> %2$s',
						__( 'ERROR', 'give' ),
						__( 'Please enter your first name.', 'give' )
					)
				);
			}
		}
	}

}

add_action( 'user_profile_update_errors', 'give_validate_user_profile', 10, 3 );

/**
 * Show Donor Information on User Profile Page.
 *
 * @since 2.0
 *
 * @param object $user User Object.
 *
 */
function give_donor_information_profile_fields( $user ) {
	$donor = Give()->donors->get_donor_by( 'user_id', $user->ID );

	// Display Donor Information, only if donor is attached with User.
	if ( ! empty( $donor->user_id ) ) :
		?>
		<tr>
			<th scope="row"><?php _e( 'Donor', 'give' ); ?></th>
			<td>
				<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ); ?>">
					<?php _e( 'View Donor Information', 'give' ); ?>
				</a>
			</td>
		</tr>
		<?php
	endif;
}

add_action( 'personal_options', 'give_donor_information_profile_fields' );
/**
 * Get Array of WP User Roles.
 *
 * @since 1.8.13
 * @return array
 */
function give_get_user_roles() {
	$user_roles = [];

	// Loop through User Roles.
	foreach ( get_editable_roles() as $role_name => $role_info ) :
		$user_roles[ $role_name ] = $role_info['name'];
	endforeach;

	return $user_roles;
}


/**
 * Ajax handle for donor address.
 *
 * @since 2.0
 * @since 2.11.0 decode url before parsing and sanitizing url when set $post.
 * @return void
 */
function __give_ajax_donor_manage_addresses() {
	// Bailout.
	if (
		empty( $_POST['form'] ) ||
		empty( $_POST['donorID'] )
	) {
		wp_send_json_error(
			[
				'error' => 1,
			]
		);
	}

	$post                  = give_clean( wp_parse_args( urldecode_deep( $_POST ) ) );
	$donorID               = absint( $post['donorID'] );
	$form_data             = give_clean( wp_parse_args( $post['form'] ) );
	$is_multi_address_type = ( 'billing' === $form_data['address-id'] || false !== strpos( $form_data['address-id'], '_' ) );
	$exploded_address_id   = explode( '_', $form_data['address-id'] );
	$address_type          = false !== strpos( $form_data['address-id'], '_' ) ?
		array_shift( $exploded_address_id ) :
		$form_data['address-id'];
	$address_id            = false !== strpos( $form_data['address-id'], '_' ) ?
		array_pop( $exploded_address_id ) :
		null;
	$response_data         = [
		'action' => $form_data['address-action'],
		'id'     => $form_data['address-id'],
	];

	// Security check.
	if ( ! wp_verify_nonce( $form_data['_wpnonce'], 'give-manage-donor-addresses' ) ) {
		wp_send_json_error(
			[
				'error'     => 1,
				'error_msg' => wp_sprintf(
					'<div class="notice notice-error"><p>%s</p></div>',
					__( 'Error: Security issue.', 'give' )
				),
			]
		);
	}

	$donor = new Give_Donor( $donorID );

	// Verify donor.
	if ( ! $donor->id ) {
		wp_send_json_error(
			[
				'error' => 3,
			]
		);
	}

	// Unset all data except address.
	unset(
		$form_data['_wpnonce'],
		$form_data['address-action'],
		$form_data['address-id']
	);

	// Process action.
	switch ( $response_data['action'] ) {

		case 'add':
			if ( ! $donor->add_address( "{$address_type}[]", $form_data ) ) {
				wp_send_json_error(
					[
						'error'     => 1,
						'error_msg' => wp_sprintf(
							'<div class="notice notice-error"><p>%s</p></div>',
							__( 'Error: Unable to save the address. Please check if address already exist.', 'give' )
						),
					]
				);
			}

			$total_addresses = count( $donor->address[ $address_type ] );

			$address_index = $is_multi_address_type ?
				$total_addresses - 1 :
				$address_type;

			$array_keys = array_keys( $donor->address[ $address_type ] );

			$address_id = $is_multi_address_type ?
				end( $array_keys ) :
				$address_type;

			$response_data['address_html'] = __give_get_format_address(
				end( $donor->address['billing'] ),
				[
					// We can add only billing address from donor screen.
					'type'  => 'billing',
					'id'    => $address_id,
					'index' => ++ $address_index,
				]
			);
			$response_data['success_msg']  = wp_sprintf(
				'<div class="notice updated"><p>%s</p></div>',
				__( 'Successfully added a new address to the donor.', 'give' )
			);

			if ( $is_multi_address_type ) {
				$response_data['id'] = "{$response_data['id']}_{$address_index}";
			}

			break;

		case 'remove':
			if ( ! $donor->remove_address( $response_data['id'] ) ) {
				wp_send_json_error(
					[
						'error'     => 2,
						'error_msg' => wp_sprintf(
							'<div class="notice notice-error"><p>%s</p></div>',
							__( 'Error: Unable to delete address.', 'give' )
						),
					]
				);
			}

			$response_data['success_msg'] = wp_sprintf(
				'<div class="notice updated"><p>%s</p></div>',
				__( 'Successfully removed a address of donor.', 'give' )
			);

			break;

		case 'update':
			if ( ! $donor->update_address( $response_data['id'], $form_data ) ) {
				wp_send_json_error(
					[
						'error'     => 3,
						'error_msg' => wp_sprintf(
							'<div class="notice notice-error"><p>%s</p></div>',
							__( 'Error: Unable to update address. Please check if address already exist.', 'give' )
						),
					]
				);
			}

			$response_data['address_html'] = __give_get_format_address(
				$is_multi_address_type ?
					$donor->address[ $address_type ][ $address_id ] :
					$donor->address[ $address_type ],
				[
					'type'  => $address_type,
					'id'    => $address_id,
					'index' => $address_id,
				]
			);
			$response_data['success_msg']  = wp_sprintf(
				'<div class="notice updated"><p>%s</p></div>',
				__( 'Successfully updated a address of donor', 'give' )
			);

			break;
	}// End switch().

	wp_send_json_success( $response_data );
}

add_action( 'wp_ajax_donor_manage_addresses', '__give_ajax_donor_manage_addresses' );

/**
 * Admin donor billing address label
 *
 * @since 2.0
 *
 * @param string $address_label
 *
 * @return string
 */
function __give_donor_billing_address_label( $address_label ) {
	$address_label = __( 'Billing Address', 'give' );

	return $address_label;
}

add_action( 'give_donor_billing_address_label', '__give_donor_billing_address_label' );

/**
 * Admin donor personal address label
 *
 * @since 2.0
 *
 * @param string $address_label
 *
 * @return string
 */
function __give_donor_personal_address_label( $address_label ) {
	$address_label = __( 'Personal Address', 'give' );

	return $address_label;
}

add_action( 'give_donor_personal_address_label', '__give_donor_personal_address_label' );

/**
 * Update Donor Information when User Profile is updated from admin.
 * Note: for internal use only.
 *
 * @since  2.0
 *
 * @param int $user_id
 *
 * @access public
 * @return bool
 */
function give_update_donor_name_on_user_update( $user_id = 0 ) {

	if ( current_user_can( 'edit_user', $user_id ) ) {

		$donor = new Give_Donor( $user_id, true );

		// Bailout, if donor doesn't exists.
		if ( ! $donor ) {
			return false;
		}

		// Get User First name and Last name.
		$first_name = ( $_POST['first_name'] ) ? give_clean( $_POST['first_name'] ) : get_user_meta( $user_id, 'first_name', true );
		$last_name  = ( $_POST['last_name'] ) ? give_clean( $_POST['last_name'] ) : get_user_meta( $user_id, 'last_name', true );
		$full_name  = strip_tags( wp_unslash( trim( "{$first_name} {$last_name}" ) ) );

		// Assign User First name and Last name to Donor.
		Give()->donors->update(
			$donor->id,
			[
				'name' => $full_name,
			]
		);
		Give()->donor_meta->update_meta( $donor->id, '_give_donor_first_name', $first_name );
		Give()->donor_meta->update_meta( $donor->id, '_give_donor_last_name', $last_name );

	}
}

add_action( 'edit_user_profile_update', 'give_update_donor_name_on_user_update', 10 );
add_action( 'personal_options_update', 'give_update_donor_name_on_user_update', 10 );


/**
 * Updates the email address of a donor record when the email on a user is updated
 * Note: for internal use only.
 *
 * @since  1.4.3
 * @access public
 *
 * @param WP_User|bool $old_user_data User data.
 *
 * @param int          $user_id User ID.
 *
 * @return bool
 */
function give_update_donor_email_on_user_update( $user_id = 0, $old_user_data = false ) {

	$donor = new Give_Donor( $user_id, true );

	if ( ! $donor ) {
		return false;
	}

	$user = get_userdata( $user_id );

	if ( ! empty( $user ) && $user->user_email !== $donor->email ) {

		$success = Give()->donors->update(
			$donor->id,
			[
				'email' => $user->user_email,
			]
		);

		if ( $success ) {
			// Update some payment meta if we need to
			$payments_array = explode( ',', $donor->payment_ids );

			if ( ! empty( $payments_array ) ) {

				foreach ( $payments_array as $payment_id ) {

					give_update_payment_meta( $payment_id, 'email', $user->user_email );

				}
			}

			/**
			 * Fires after updating donor email on user update.
			 *
			 * @since 1.4.3
			 *
			 * @param Give_Donor $donor Give donor object.
			 *
			 * @param WP_User    $user WordPress User object.
			 */
			do_action( 'give_update_donor_email_on_user_update', $user, $donor );

		}
	}

}

add_action( 'profile_update', 'give_update_donor_email_on_user_update', 10, 2 );


/**
 * Flushes Give's cache.
 */
function give_cache_flush() {
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die();
	}

	$result = Give_Cache::flush_cache();

	if ( $result ) {
		wp_send_json_success(
			[
				'message' => __( 'Cache flushed successfully.', 'give' ),
			]
		);
	} else {
		wp_send_json_error(
			[
				'message' => __( 'An error occurred while flushing the cache.', 'give' ),
			]
		);
	}
}

add_action( 'wp_ajax_give_cache_flush', 'give_cache_flush', 10, 0 );

/**
 * Admin notices for errors
 * note: only for internal use
 *
 * @access public
 * @since  2.5.0
 * @return void
 */
function give_license_notices() {

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		return;
	}

	// Do not show licenses notices on license tab.
	if ( Give_Admin_Settings::is_setting_page( 'licenses' ) ) {
		return;
	}

	$give_plugins          = give_get_plugins( [ 'only_premium_add_ons' => true ] );
	$give_licenses         = get_option( 'give_licenses', [] );
	$notice_data           = [];
	$license_data          = [];
	$invalid_license_count = 0;
	$addons_with_license   = [];

	// Loop through Give licenses to find license status.
	foreach ( $give_licenses as $key => $give_license ) {
		if ( empty( $license_data[ $give_license['license'] ] ) ) {
			$license_data[ $give_license['license'] ] = [
				'count'   => 0,
				'add-ons' => [],
			];
		}

		// Setup data for all access pass.
		if ( $give_license['is_all_access_pass'] ) {
			$addons_list = wp_list_pluck( $give_license['download'], 'plugin_slug' );
			foreach ( $addons_list as $item ) {
				$license_data[ $give_license['license'] ]['add-ons'][] = $addons_with_license[] = $item;
			}
		} else {
			$license_data[ $give_license['license'] ]['add-ons'][] = $addons_with_license[] = $give_license['plugin_slug'];
		}

		$license_data[ $give_license['license'] ]['count'] += 1;
	}

	// Set data for inactive add-ons.
	$inactive_addons = array_diff( wp_list_pluck( $give_plugins, 'Dir' ), $addons_with_license );

	$license_data['inactive'] = [
		'count'   => count( $inactive_addons ),
		'add-ons' => array_values( $inactive_addons ),
	];

	// Unset active license add-ons as not required.
	unset( $license_data['valid'] );

	// Combine site inactive with inactive and unset site_inactive because already merged information with inactive
	if ( ! empty( $license_data['site_inactive'] ) ) {
		$license_data['inactive']['count']   += $license_data['site_inactive']['count'];
		$license_data['inactive']['add-ons'] += $license_data['site_inactive']['add-ons'];

		unset( $license_data['site_inactive'] );
	}

	// Loop through license data.
	foreach ( $license_data as $key => $license ) {
		if ( ! $license['count'] ) {
			continue;
		}

		$notice_data[ $key ] = sprintf(
			'%1$s %2$s',
			$license['count'],
			$key
		);

		// This will contain sum of count expect license with valid status.
		$invalid_license_count += $license['count'];
	}

	// Prepare license notice description.
	$prepared_notice_status = implode( ' , ', $notice_data );
	$prepared_notice_status = 2 <= count( $notice_data )
		? substr_replace( $prepared_notice_status, 'and', strrpos( $prepared_notice_status, ',' ), 1 )
		: $prepared_notice_status;

	$notice_description = sprintf(
		_n(
			'Your GiveWP add-on is not receiving critical updates and new features because you have %1$s license key. Please <a href="%2$s" title="%3$s">activate your license</a> to receive updates and <a href="%4$s" target="_blank" title="%5$s">priority support</a>',
			'Your GiveWP add-ons are not receiving critical updates and new features because you have %1$s license keys. Please <a href="%2$s" title="%3$s">activate your license</a> to receive updates and <a href="%4$s" target="_blank" title="%5$s">priority support</a>',
			$invalid_license_count,
			'give'
		),
		$prepared_notice_status,
		admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=licenses' ),
		__( 'Activate License', 'give' ),
		esc_url( 'http://docs.givewp.com/pb-priority-support' ),
		__( 'Priority Support', 'give' )
	);

	// Check by add-on if any give add-on activated without license.
	// Do not show this notice if add-on activated with in 3 days.
	$is_required_days_past = current_time( 'timestamp' ) > ( Give_Cache_Setting::get_option( 'give_addon_last_activated' ) + ( 3 * DAY_IN_SECONDS ) );

	// Default license notice arguments.
	$license_notice_args = [
		'id'               => 'give-invalid-expired-license',
		'type'             => 'error',
		'description'      => $notice_description,
		'dismissible_type' => 'user',
		'dismiss_interval' => 'shortly',
	];

	// Register Notices.
	if ( $invalid_license_count && $is_required_days_past ) {
		Give()->notices->register_notice( $license_notice_args );
	}
}

add_action( 'admin_notices', 'give_license_notices' );


/**
 * Log give addon activation time
 *
 * @since 2.5.0
 *
 * @param $network_wide
 *
 * @param $plugin
 */
function give_log_addon_activation_time( $plugin, $network_wide ) {
	if ( $network_wide ) {
		return;
	}

	$plugin_data = give_get_plugins( [ 'only_premium_add_ons' => true ] );
	$plugin_data = ! empty( $plugin_data[ $plugin ] ) ? $plugin_data[ $plugin ] : [];

	if ( $plugin_data ) {
		update_option( 'give_addon_last_activated', current_time( 'timestamp' ), 'no' );
	}
}

add_action( 'activate_plugin', 'give_log_addon_activation_time', 10, 2 );


/**
 * Hide all admin notice from add-ons page
 *
 * Note: only for internal use
 *
 * @since 2.5.0
 */
function give_hide_notices_on_add_ons_page() {
	$page = ! empty( $_GET['page'] ) ? give_clean( $_GET['page'] ) : '';

	// Bailout.
	if ( 'give-addons' !== $page ) {
		return;
	}

	remove_all_actions( 'admin_notices' );
}

add_action( 'in_admin_header', 'give_hide_notices_on_add_ons_page', 999 );


/**
 * Admin JS
 *
 * @since 2.5.0
 */
function give_admin_quick_js() {
	if ( is_multisite() && is_blog_admin() ) {
		?>
		<script>
			jQuery(document).ready(function ($) {
				var $updateNotices = $('[id$="-update"] ', '.wp-list-table');

				if ($updateNotices.length) {
					$.each($updateNotices, function (index, $updateNotice) {
						$updateNotice = $($updateNotice);
						$updateNotice.prev().addClass('update');
					});
				}
			});
		</script>
		<?php
	}
}

add_action( 'admin_head', 'give_admin_quick_js' );

/**
 * Add Admin addon menu related scripts
 *
 * @since 2.6.0
 */
function give_admin_addon_menu_inline_scripts() {
	?>
	<script>
		(function ($) {
			const $addonLink = $('#menu-posts-give_forms a[href^="https://go.givewp.com"]');
			$addonLink.attr('target', '_blank');

			<?php if ( empty( give_get_plugins( [ 'only_premium_add_ons' => true ] ) ) ) : ?>
			$addonLink.addClass('give-highlight');
			$addonLink.prepend('<span class="dashicons dashicons-star-filled"></span>');
			<?php endif; ?>
		})(jQuery)
	</script>
	<style>
		#menu-posts-give_forms a[href^="https://go.givewp.com"].give-highlight {
			color: rgb(43, 194, 83);
			font-weight: 700;
			vertical-align: top;
			text-shadow: 0 1px 2px #00000080;
		}

		#menu-posts-give_forms a[href^="https://go.givewp.com"].give-highlight span.dashicons {
			font-size: 14px !important;
			width: auto;
			height: 18px;
			padding-right: 3px;
			vertical-align: middle;
		}
	</style>
	<?php
}

add_action( 'admin_footer', 'give_admin_addon_menu_inline_scripts' );

/**
 * Handle akismet_deblacklist_spammed_email_handler give-action
 *
 * @since 2.5.14
 *
 * @param array $get
 *
 */
function give_akismet_deblacklist_spammed_email_handler( $get ) {
	$email  = ! empty( $get['email'] ) && is_email( $get['email'] ) ? give_clean( $get['email'] ) : '';
	$log    = ! empty( $get['log'] ) ? absint( $get['log'] ) : '';
	$action = "give_akismet_deblacklist_spammed_email_{$email}";

	check_admin_referer( $action );
	$emails = give_akismet_get_whitelisted_emails();

	if ( ! in_array( $email, $emails, true ) ) {
		array_unshift( $emails, $email );

		give_update_option( 'akismet_whitelisted_email_addresses', $emails );

		// Redirect to Akismet setting page.
		wp_safe_redirect( 'wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=advanced&section=akismet-spam-protection&give-message=akismet-deblacklisted-email' );
	}
}

add_action( 'give_akismet_deblacklist_spammed_email', 'give_akismet_deblacklist_spammed_email_handler' );

/**
 * Add Custom setting view for form them setting panel
 *
 * @since 2.7.0
 */
function give_render_form_theme_setting_panel() {
	require_once GIVE_PLUGIN_DIR . 'src/Views/Admin/Form/Metabox-Settings.php';
}

add_action( 'give_post_form_template_options_settings', 'give_render_form_theme_setting_panel' );



