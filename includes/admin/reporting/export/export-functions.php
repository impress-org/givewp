<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from Give
 *
 * @package     Give
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once GIVE_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';
require_once GIVE_PLUGIN_DIR . 'includes/admin/reporting/export/export-actions.php';

/**
 * Process batch exports via ajax
 *
 * @since 1.5
 * @return void
 */
function give_do_ajax_export() {

	require_once GIVE_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export.php';

	parse_str( $_POST['form'], $form );

	$_REQUEST = $form = (array) $form;

	if ( ! wp_verify_nonce( $_REQUEST['give_ajax_export'], 'give_ajax_export' ) ) {
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

	$step   = absint( $_POST['step'] );
	$class  = sanitize_text_field( $form['give-export-class'] );

	$export = new $class( $step );

	if ( ! $export->can_export() ) {
		die( '-1' );
	}

	if ( ! $export->is_writable ) {
		$json_args = array(
			'error'   => true,
			'message' => esc_html__( 'Export location or file not writable.', 'give' )
		);
		echo json_encode($json_args);
		exit;
	}

	$export->set_properties( $_REQUEST );

	$export->pre_fetch();

	$ret = $export->process_step( $step );

	$percentage = $export->get_percentage_complete();

	if ( $ret ) {

		$step += 1;
		echo json_encode( array( 'step' => $step, 'percentage' => $percentage ) );
		exit;

	} elseif ( true === $export->is_empty ) {

		echo json_encode( array(
			'error'   => true,
			'message' => esc_html__( 'No data found for export parameters.', 'give' )
		) );
		exit;

	} elseif ( true === $export->done && true === $export->is_void ) {

		$message = ! empty( $export->message ) ? $export->message : esc_html__( 'Batch Processing Complete', 'give' );
		echo json_encode( array( 'success' => true, 'message' => $message ) );
		exit;

	} else {
		
		$args = array_merge( $_REQUEST, array(
			'step'        => $step,
			'class'       => $class,
			'nonce'       => wp_create_nonce( 'give-batch-export' ),
			'give_action' => 'form_batch_export',
		) );

		$download_url = add_query_arg( $args, admin_url() );

		echo json_encode( array( 'step' => 'done', 'url' => $download_url ) );
		exit;

	}
}

add_action( 'wp_ajax_give_do_ajax_export', 'give_do_ajax_export' );
