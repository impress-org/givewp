<?php
/**
 * Export Class
 *
 * This is the base class for all export methods. Each data export type (donors, payments, etc) extend this class
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Export Class
 *
 * @since 1.0
 */
class Give_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.0
	 */
	public $export_type = 'default';

	/**
	 * Can we export?
	 *
	 * @access public
	 * @since  1.0
	 * @return bool Whether we can export or not
	 */
	public function can_export() {
		return (bool) apply_filters( 'give_export_capability', current_user_can( 'export_give_reports' ) );
	}

	/**
	 * Set the export headers
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=give-export-' . $this->export_type . '-' . date( 'm-d-Y' ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since  1.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'id'   => __( 'ID', 'give' ),
			'date' => __( 'Date', 'give' )
		);

		return $cols;
	}

	/**
	 * Retrieve the CSV columns
	 *
	 * @access public
	 * @since  1.0
	 * @return array $cols Array of the columns
	 */
	public function get_csv_cols() {
		$cols = $this->csv_cols();

		return apply_filters( 'give_export_csv_cols_' . $this->export_type, $cols );
	}

	/**
	 * Output the CSV columns
	 *
	 * @access public
	 * @since  1.0
	 * @uses   Give_Export::get_csv_cols()
	 * @return void
	 */
	public function csv_cols_out() {
		$cols = $this->get_csv_cols();
		$i    = 1;
		foreach ( $cols as $col_id => $column ) {
			echo '"' . addslashes( $column ) . '"';
			echo $i == count( $cols ) ? '' : ',';
			$i ++;
		}
		echo "\r\n";
	}

	/**
	 * Get the data being exported
	 *
	 * @access public
	 * @since  1.0
	 * @return array $data Data for Export
	 */
	public function get_data() {
		// Just a sample data array
		$data = array(
			0 => array(
				'id'   => '',
				'data' => date( 'F j, Y' )
			),
			1 => array(
				'id'   => '',
				'data' => date( 'F j, Y' )
			)
		);

		$data = apply_filters( 'give_export_get_data', $data );
		$data = apply_filters( 'give_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Output the CSV rows
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function csv_rows_out() {
		$data = $this->get_data();

		$cols = $this->get_csv_cols();

		// Output each row
		foreach ( $data as $row ) {
			$i = 1;
			foreach ( $row as $col_id => $column ) {
				// Make sure the column is valid
				if ( array_key_exists( $col_id, $cols ) ) {
					echo '"' . addslashes( $column ) . '"';
					echo $i == count( $cols ) ? '' : ',';
					$i ++;
				}
			}
			echo "\r\n";
		}
	}

	/**
	 * Perform the export
	 *
	 * @access public
	 * @since  1.0
	 * @uses   Give_Export::can_export()
	 * @uses   Give_Export::headers()
	 * @uses   Give_Export::csv_cols_out()
	 * @uses   Give_Export::csv_rows_out()
	 * @return void
	 */
	public function export() {
		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		// Set headers
		$this->headers();

		// Output CSV columns (headers)
		$this->csv_cols_out();

		// Output CSV rows
		$this->csv_rows_out();

		give_die();
	}
}
