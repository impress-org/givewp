<?php
/**
 * Exports Functions
 *
 * These functions are used for exporting data from Give
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



/**
 * Process batch exports via ajax
 *
 * @since 1.5
 * @return void
 */
function give_do_ajax_export() {

	require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export.php';

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

	/* @var Give_Batch_Export $export */
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

	$export->set_properties( give_clean( $_REQUEST ) );

	$export->pre_fetch();

	$ret = $export->process_step();

	$percentage = $export->get_percentage_complete();

	if ( $ret ) {

		$step += 1;
		$json_data = array(
			'step' => $step,
			'percentage' => $percentage
		);

	} elseif ( true === $export->is_empty ) {

		$json_data = array(
			'error'   => true,
			'message' => esc_html__( 'No data found for export parameters.', 'give' )
		);

	} elseif ( true === $export->done && true === $export->is_void ) {

		$message = ! empty( $export->message ) ?
			$export->message :
			esc_html__( 'Batch Processing Complete', 'give' );

		$json_data = array(
			'success' => true,
			'message' => $message
		);

	} else {
		
		$args = array_merge( $_REQUEST, array(
			'step'        => $step,
			'class'       => $class,
			'nonce'       => wp_create_nonce( 'give-batch-export' ),
			'give_action' => 'form_batch_export',
		) );

		$json_data = array(
			'step' => 'done',
			'url' => add_query_arg( $args, admin_url() )
		);

	}

	$export->unset_properties( give_clean( $_REQUEST ), $export );
	echo json_encode( $json_data );
	exit;
}

add_action( 'wp_ajax_give_do_ajax_export', 'give_do_ajax_export' );
