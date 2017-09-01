<?php
/**
 * Admin Actions
 *
 * @package     Give
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
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
	if ( ! isset( $_POST['wp_editor'] ) ) {
		die();
	}

	$wp_editor                     = json_decode( base64_decode( $_POST['wp_editor'] ), true );
	$wp_editor[2]['textarea_name'] = $_POST['textarea_name'];

	wp_editor( $wp_editor[0], $_POST['wp_editor_id'], $wp_editor[2] );

	die();
}

add_action( 'wp_ajax_give_load_wp_editor', 'give_load_wp_editor' );


/**
 * Redirect admin to clean url give admin pages.
 *
 * @since 1.8
 *
 * @return bool
 */
function give_redirect_to_clean_url_admin_pages() {
	// Give admin pages.
	$give_pages = array(
		'give-payment-history',
		'give-donors',
		'give-reports',
		'give-tools'
	);

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
				array( '_wp_http_referer', '_wpnonce' ),
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
 *
 * @return void
 */
function give_hide_outdated_php_notice() {

	if ( ! isset( $_POST['_give_hide_outdated_php_notices_shortly'] ) ) {
		give_die();
	}

	// Transient key name.
	$transient_key = "_give_hide_outdated_php_notices_shortly";

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

	// Add payment bulk notice.
	if (
		current_user_can( 'edit_give_payments' )
		&& isset( $_GET['action'] )
		&& ! empty( $_GET['action'] )
		&& isset( $_GET['payment'] )
		&& ! empty( $_GET['payment'] )
	) {
		$payment_count = isset( $_GET['payment'] ) ? count( $_GET['payment'] ) : 0;

		switch ( $_GET['action'] ) {
			case 'delete':
				Give()->notices->register_notice( array(
					'id'          => 'bulk_action_delete',
					'type'        => 'updated',
					'description' => sprintf(
						_n(
							'Successfully deleted one transaction.',
							'Successfully deleted %d transactions.',
							$payment_count,
							'give'
						),
						$payment_count ),
					'show'        => true,
				) );

				break;

			case 'resend-receipt':
				Give()->notices->register_notice( array(
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
				) );
				break;
		}
	}

	// Add give message notices.
	if ( ! empty( $_GET['give-message'] ) ) {
		// Donation reports errors.
		if ( current_user_can( 'view_give_reports' ) ) {
			switch ( $_GET['give-message'] ) {
				case 'donation_deleted' :
					Give()->notices->register_notice( array(
						'id'          => 'give-donation-deleted',
						'type'        => 'updated',
						'description' => __( 'The donation has been deleted.', 'give' ),
						'show'        => true,
					) );
					break;
				case 'email_sent' :
					Give()->notices->register_notice( array(
						'id'          => 'give-payment-sent',
						'type'        => 'updated',
						'description' => __( 'The donation receipt has been resent.', 'give' ),
						'show'        => true,
					) );
					break;
				case 'refreshed-reports' :
					Give()->notices->register_notice( array(
						'id'          => 'give-refreshed-reports',
						'type'        => 'updated',
						'description' => __( 'The reports cache has been cleared.', 'give' ),
						'show'        => true,
					) );
					break;
				case 'donation-note-deleted' :
					Give()->notices->register_notice( array(
						'id'          => 'give-donation-note-deleted',
						'type'        => 'updated',
						'description' => __( 'The donation note has been deleted.', 'give' ),
						'show'        => true,
					) );
					break;
			}
		}

		// Give settings notices and errors.
		if ( current_user_can( 'manage_give_settings' ) ) {
			switch ( $_GET['give-message'] ) {
				case 'settings-imported' :
					Give()->notices->register_notice( array(
						'id'          => 'give-settings-imported',
						'type'        => 'updated',
						'description' => __( 'The settings have been imported.', 'give' ),
						'show'        => true,
					) );
					break;
				case 'api-key-generated' :
					Give()->notices->register_notice( array(
						'id'          => 'give-api-key-generated',
						'type'        => 'updated',
						'description' => __( 'API keys have been generated.', 'give' ),
						'show'        => true,
					) );
					break;
				case 'api-key-exists' :
					Give()->notices->register_notice( array(
						'id'          => 'give-api-key-exists',
						'type'        => 'updated',
						'description' => __( 'The specified user already has API keys.', 'give' ),
						'show'        => true,
					) );
					break;
				case 'api-key-regenerated' :
					Give()->notices->register_notice( array(
						'id'          => 'give-api-key-regenerated',
						'type'        => 'updated',
						'description' => __( 'API keys have been regenerated.', 'give' ),
						'show'        => true,
					) );
					break;
				case 'api-key-revoked' :
					Give()->notices->register_notice( array(
						'id'          => 'give-api-key-revoked',
						'type'        => 'updated',
						'description' => __( 'API keys have been revoked.', 'give' ),
						'show'        => true,
					) );
					break;
				case 'sent-test-email' :
					Give()->notices->register_notice( array(
						'id'          => 'give-sent-test-email',
						'type'        => 'updated',
						'description' => __( 'The test email has been sent.', 'give' ),
						'show'        => true,
					) );
					break;
				case 'matched-success-failure-page':
					Give()->notices->register_notice( array(
						'id'          => 'give-matched-success-failure-page',
						'type'        => 'updated',
						'description' => __( 'You cannot set the success and failed pages to the same page', 'give' ),
						'show'        => true,
					) );
					break;
			}
		}
		// Payments errors.
		if ( current_user_can( 'edit_give_payments' ) ) {
			switch ( $_GET['give-message'] ) {
				case 'note-added' :
					Give()->notices->register_notice( array(
						'id'          => 'give-note-added',
						'type'        => 'updated',
						'description' => __( 'The donation note has been added.', 'give' ),
						'show'        => true,
					) );
					break;
				case 'payment-updated' :
					Give()->notices->register_notice( array(
						'id'          => 'give-payment-updated',
						'type'        => 'updated',
						'description' => __( 'The donation has been updated.', 'give' ),
						'show'        => true,
					) );
					break;
			}
		}

		// Donor Notices.
		if ( current_user_can( 'edit_give_payments' ) ) {
			switch ( $_GET['give-message'] ) {
				case 'donor-deleted' :
					Give()->notices->register_notice( array(
						'id'          => 'give-donor-deleted',
						'type'        => 'updated',
						'description' => __( 'The donor has been deleted.', 'give' ),
						'show'        => true,
					) );
					break;

				case 'email-added' :
					Give()->notices->register_notice( array(
						'id'          => 'give-donor-email-added',
						'type'        => 'updated',
						'description' => __( 'Donor email added.', 'give' ),
						'show'        => true,
					) );
					break;

				case 'email-removed' :
					Give()->notices->register_notice( array(
						'id'          => 'give-donor-email-removed',
						'type'        => 'updated',
						'description' => __( 'Donor email removed.', 'give' ),
						'show'        => true,
					) );
					break;

				case 'email-remove-failed' :
					Give()->notices->register_notice( array(
						'id'          => 'give-donor-email-remove-failed',
						'type'        => 'updated',
						'description' => __( 'Failed to remove donor email.', 'give' ),
						'show'        => true,
					) );
					break;

				case 'primary-email-updated' :
					Give()->notices->register_notice( array(
						'id'          => 'give-donor-primary-email-updated',
						'type'        => 'updated',
						'description' => __( 'Primary email updated for donor.', 'give' ),
						'show'        => true,
					) );
					break;

				case 'primary-email-failed' :
					Give()->notices->register_notice( array(
						'id'          => 'give-donor-primary-email-failed',
						'type'        => 'updated',
						'description' => __( 'Failed to set primary email.', 'give' ),
						'show'        => true,
					) );
					break;
			}
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

	// Add the main siteadmin menu item.
	$wp_admin_bar->add_menu( array(
		'id'     => 'give-test-notice',
		'href'   => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways' ),
		'parent' => 'top-secondary',
		'title'  => esc_html__( 'Give Test Mode Active', 'give' ),
		'meta'   => array( 'class' => 'give-test-mode-active' ),
	) );

	return true;
}

add_action( 'admin_bar_menu', '_give_show_test_mode_notice_in_admin_bar', 1000, 1 );

/**
 * Add Link to Import page in from donation archive and donation single page
 *
 * @since 1.8.13
 */
function give_import_page_link_callback() {
	?>
	<a href="<?php echo esc_url( give_import_page_url() ); ?>"
	   class="page-import-action page-title-action"><?php esc_html_e( 'Import Donations', 'give' ); ?></a>

	<style type="text/css">
		<?php
		// Check if view donation single page only.
		if ( ! empty( $_REQUEST['view'] ) && 'view-payment-details' === (string) give_clean( $_REQUEST['view'] ) && 'give-payment-history' === give_clean( $_REQUEST['page'] ) ) {
			?>
		.wrap #transaction-details-heading {
			display: inline-block;
		}

		<?php
	} else {
		?>
		/* So the "New Donation" button aligns with the wp-admin h1 tag */
		.wrap > h1 {
			display: inline-block;
			margin-right: 5px;
		}

		<?php
	} ?>
	</style>
	<?php
}


add_action( 'give_payments_page_top', 'give_import_page_link_callback', 11 );
add_action( 'give_view_order_details_before', 'give_import_page_link_callback', 11 );

/**
 * Load donation import ajax callback
 * Fire when importing from CSV start
 *
 * @since  1.8.13
 *
 * @return json $json_data
 */
function give_donation_import_callback() {
	$import_setting = array();
	$fields         = isset( $_POST['fields'] ) ? $_POST['fields'] : null;

	parse_str( $fields );

	$import_setting['create_user'] = $create_user;
	$import_setting['mode']        = $mode;
	$import_setting['delimiter']   = $delimiter;
	$import_setting['csv']         = $csv;
	$import_setting['delete_csv']  = $delete_csv;

	// Parent key id.
	$main_key = maybe_unserialize( $main_key );

	$current    = absint( $_REQUEST['current'] );
	$total_ajax = absint( $_REQUEST['total_ajax'] );
	$start      = absint( $_REQUEST['start'] );
	$end        = absint( $_REQUEST['end'] );
	$next       = absint( $_REQUEST['next'] );
	$total      = absint( $_REQUEST['total'] );
	$per_page   = absint( $_REQUEST['per_page'] );
	if ( empty( $delimiter ) ) {
		$delimiter = ',';
	}

	// processing done here.
	$raw_data = give_get_donation_data_from_csv( $csv, $start, $end, $delimiter );
	$raw_key  = maybe_unserialize( $mapto );

	//Prevent normal emails
	remove_action( 'give_complete_donation', 'give_trigger_donation_receipt', 999 );
	remove_action( 'give_insert_user', 'give_new_user_notification', 10 );
	remove_action( 'give_insert_payment', 'give_payment_save_page_data' );

	foreach ( $raw_data as $row_data ) {
		give_save_import_donation_to_db( $raw_key, $row_data, $main_key, $import_setting );
	}

	// Check if function exists or not.
	if ( function_exists( 'give_payment_save_page_data' ) ) {
		add_action( 'give_insert_payment', 'give_payment_save_page_data' );
	}
	add_action( 'give_insert_user', 'give_new_user_notification', 10, 2 );
	add_action( 'give_complete_donation', 'give_trigger_donation_receipt', 999 );

	if ( $next == false ) {
		$json_data = array(
			'success' => true,
			'message' => __( 'All donation uploaded successfully!', 'give' ),
		);
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
		$json_data = array(
			'raw_data' => $raw_data,
			'raw_key'  => $raw_key,
			'next'     => $next,
			'start'    => $index_start,
			'end'      => $index_end,
			'last'     => $last,
		);
	}

	$url              = give_import_page_url( array(
		'step'       => '4',
		'csv'        => $csv,
		'total'      => $total,
		'delete_csv' => $import_setting['delete_csv'],
		'success'    => ( isset( $json_data['success'] ) ? $json_data['success'] : '' ),
	) );
	$json_data['url'] = $url;

	$current ++;
	$json_data['current'] = $current;

	$percentage              = ( 100 / ( $total_ajax + 1 ) ) * $current;
	$json_data['percentage'] = $percentage;

	$json_data = apply_filters( 'give_import_ajax_responces', $json_data, $fields );
	wp_die( json_encode( $json_data ) );
}

add_action( 'wp_ajax_give_donation_import', 'give_donation_import_callback' );


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
 * Get Array of WP User Roles.
 *
 * @since 1.8.13
 *
 * @return array
 */
function give_get_user_roles() {
	$user_roles = array();

	// Loop through User Roles.
	foreach ( get_editable_roles() as $role_name => $role_info ):
		$user_roles[ $role_name ] = $role_info['name'];
	endforeach;

	return $user_roles;
}