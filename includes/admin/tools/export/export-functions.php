<?php
/**
 * Exports Functions
 *
 * These functions are used for exporting data from Give
 *
 * @package     Give
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Process batch exports via ajax
 *
 * @since TBD Validate and sanitize the posted form, step and file name.
 * @since 2.21.0 Sanitize file name. Allow plain file name only.
 * @since 1.5
 * @return void
 */
function give_do_ajax_export() {

	require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export.php';

	// The nonce is a field inside the posted form string; it is verified right below, before anything else runs.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- the posted string is URL-encoded form data for parse_str(); it is unslashed later by give_clean( $_REQUEST ) and each field is sanitized where it is used.
	parse_str( isset( $_POST['form'] ) ? $_POST['form'] : '', $form );

	$_REQUEST = $form = (array) $form;

	$export_nonce = isset( $form['give_ajax_export'] ) ? sanitize_text_field( wp_unslash( $form['give_ajax_export'] ) ) : '';

	if (
		! wp_verify_nonce( $export_nonce, 'give_ajax_export' ) ||
		! current_user_can( 'manage_give_settings' )
    ) {
		die( '-2' );
	}

	/**
	 * Fires before batch export.
	 *
	 * @since 1.5
	 *
	 * @param string $class Export class.
	 */
	do_action( 'give_batch_export_class_include', $form['give-export-class'] );

    if(  ! is_subclass_of( $form['give-export-class'], \Give_Batch_Export::class ) ) {
        die(-2);
    }

	$step     = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above from the posted form (give_ajax_export).
	$class    = sanitize_text_field( $form['give-export-class'] );
	$filename = isset( $_POST['file_name'] ) ? // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above from the posted form (give_ajax_export).
        basename(sanitize_file_name( wp_unslash( $_POST['file_name'] ) ), '.csv') :
        null;

	/* @var Give_Batch_Export $export */
	$export = new $class( $step, $filename );

	if ( ! $export->can_export() ) {
		die( '-1' );
	}

	if ( ! $export->is_writable ) {
		$json_args = [
			'error'   => true,
			'message' => esc_html__( 'Export location or file not writable.', 'give' ),
		];
		echo json_encode( $json_args );
		exit;
	}

	$export->set_properties( give_clean( $_REQUEST ) );

	$export->pre_fetch();

	$ret = $export->process_step();

	$percentage = $export->get_percentage_complete();

	if ( $ret ) {

		$step     += 1;
		$json_data = [
			'step'       => $step,
			'percentage' => $percentage,
			'file_name'  => $export->filename,
		];

	} elseif ( true === $export->is_empty ) {

		$json_data = [
			'error'   => true,
			'message' => esc_html__( 'No data found for export parameters.', 'give' ),
		];

	} elseif ( true === $export->done && true === $export->is_void ) {

		$message = ! empty( $export->message ) ?
			$export->message :
			esc_html__( 'Batch Processing Complete', 'give' );

		$json_data = [
			'success' => true,
			'message' => $message,
		];

	} else {

		$args = array_merge(
			$_REQUEST,
			[
				'step'        => $step,
				'class'       => $class,
				'nonce'       => wp_create_nonce( 'give-batch-export' ),
				'give_action' => 'form_batch_export',
				'file_name'   => $export->filename,
			]
		);

		$json_data = [
			'step' => 'done',
			'url'  => esc_url_raw(add_query_arg( $args, admin_url() )),
		];

	}

	$export->unset_properties( give_clean( $_REQUEST ), $export );
	echo json_encode( $json_data );
	exit;
}

add_action( 'wp_ajax_give_do_ajax_export', 'give_do_ajax_export' );


/**
 * This function is used to define default columns for export.
 *
 * Note: This function is for internal purposes only.
 * Use filter "give_export_donors_get_default_columns" instead.
 *
 * @since 3.12.1 add donor_phone_number column.
 * @since 2.2.6
 *
 * @return array
 */
function give_export_donors_get_default_columns() {

	$default_columns = [
		'full_name'          => __( 'Name', 'give' ),
		'email'              => __( 'Email', 'give' ),
		'address'            => __( 'Address', 'give' ),
		'userid'             => __( 'User ID', 'give' ),
		'donor_created_date' => __( 'Donor Created Date', 'give' ),
        'donor_phone_number' => __( 'Donor Phone Number', 'give' ),
        'donations'          => __( 'Number of donations', 'give' ),
		'donation_sum'       => __( 'Total Donated', 'give' ),
	];

	/**
	 * This filter will be used to define default columns for export.
	 *
	 * @since 2.2.6
	 */
	return apply_filters( 'give_export_donors_get_default_columns', $default_columns );
}
