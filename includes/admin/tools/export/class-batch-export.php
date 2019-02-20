<?php
/**
 * Batch Export Class
 *
 * This is the base class for all batch export methods. Each data export type (donors, payments, etc) extend this class.
 *
 * @package     Give
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Batch_Export Class
 *
 * @since 1.5
 */
class Give_Batch_Export extends Give_Export {

	/**
	 * The file the data is stored in.
	 *
	 * @since 1.5
	 */
	private $file;

	/**
	 * The name of the file the data is stored in.
	 *
	 * @since 1.5
	 */
	public $filename;

	/**
	 * The file type, typically .csv
	 *
	 * @since 1.5
	 */
	public $filetype;

	/**
	 * The current step being processed.
	 *
	 * @since 1.5
	 */
	public $step;

	/**
	 * Start date, Y-m-d H:i:s
	 *
	 * @since 1.5
	 */
	public $start;

	/**
	 * End date, Y-m-d H:i:s
	 *
	 * @since 1.5
	 */
	public $end;

	/**
	 * Status to export.
	 *
	 * @since 1.5
	 */
	public $status;

	/**
	 * Form to export data for.
	 *
	 * @since 1.5
	 */
	public $form = null;

	/**
	 * Form Price ID to export data for.
	 *
	 * @since 1.5
	 */
	public $price_id = null;

	/**
	 * Is the export file writable.
	 *
	 * @since 1.5
	 */
	public $is_writable = true;

	/**
	 *  Is the export file empty.
	 *
	 * @since 1.5
	 */
	public $is_empty = false;

	/**
	 *
	 * @since 1.8.9
	 */
	public $is_void = false;

	/**
	 *  Is the export file complete.
	 *
	 * @since 1.8.9
	 */
	public $done = false;

	/**
	 * Give_Batch_Export constructor.
	 *
	 * @param int $_step
	 */
	public function __construct( $_step = 1 ) {

		$upload_dir     = wp_upload_dir();
		$this->filetype = '.csv';
		$this->filename = 'give-' . $this->export_type . $this->filetype;
		$this->file     = trailingslashit( $upload_dir['basedir'] ) . $this->filename;

		if ( ! is_writeable( $upload_dir['basedir'] ) ) {
			$this->is_writable = false;
		}

		$this->step = $_step;
		$this->done = false;
	}

	/**
	 * Process a step.
	 *
	 * @since 1.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( esc_html__( 'You do not have permission to export data.', 'give' ), esc_html__( 'Error', 'give' ), array(
				'response' => 403,
			) );
		}

		if ( $this->step < 2 ) {

			// Make sure we start with a fresh file on step 1.
			@unlink( $this->file );
			$this->print_csv_cols();
		}

		$this->print_csv_rows();

		return 100 !== $this->get_percentage_complete();
	}

	/**
	 * Output the CSV columns.
	 *
	 * @access public
	 * @since  1.5
	 * @uses   Give_Export::get_csv_cols()
	 * @return string
	 */
	public function print_csv_cols() {

		$col_data = '';
		$cols     = $this->get_csv_cols();
		$i        = 1;
		foreach ( $cols as $col_id => $column ) {
			$col_data .= '"' . addslashes( $column ) . '"';
			$col_data .= $i == count( $cols ) ? '' : ',';
			$i ++;
		}
		$col_data .= "\r\n";

		$this->stash_step_data( $col_data );

		return $col_data;

	}

	/**
	 * Print the CSV rows for the current step.
	 *
	 * @access public
	 * @since  1.5
	 * @return string|false
	 */
	public function print_csv_rows() {

		$row_data = '';
		$data     = $this->get_data();
		$cols     = $this->get_csv_cols();

		if ( $data ) {

			// Output each row
			foreach ( $data as $row ) {
				$i = 1;
				foreach ( $row as $col_id => $column ) {
					// Make sure the column is valid
					if ( array_key_exists( $col_id, $cols ) ) {
						$row_data .= '"' . addslashes( preg_replace( '/"/', "'", $column ) ) . '"';
						$row_data .= $i == count( $cols ) ? '' : ',';
						$i ++;
					}
				}
				$row_data .= "\r\n";
			}

			$this->stash_step_data( $row_data );

			return $row_data;
		}

		return false;
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {
		return 100;
	}

	/**
	 * Retrieve the file data is written to.
	 *
	 * @since 1.5
	 * @return string
	 */
	protected function get_file() {

		$file = '';

		if ( @file_exists( $this->file ) ) {

			if ( ! is_writeable( $this->file ) ) {
				$this->is_writable = false;
			}

			$file = @file_get_contents( $this->file );

		} else {

			@file_put_contents( $this->file, '' );
			@chmod( $this->file, 0664 );

		}

		return $file;
	}

	/**
	 * Append data to export file.
	 *
	 * @since 1.5
	 *
	 * @param $data string The data to add to the file.
	 *
	 * @return void
	 */
	protected function stash_step_data( $data = '' ) {

		$file = $this->get_file();
		$file .= $data;
		@file_put_contents( $this->file, $file );

		// If we have no rows after this step, mark it as an empty export.
		$file_rows    = file( $this->file, FILE_SKIP_EMPTY_LINES );
		$default_cols = $this->get_csv_cols();
		$default_cols = empty( $default_cols ) ? 0 : 1;

		$this->is_empty = count( $file_rows ) == $default_cols ? true : false;

	}

	/**
	 * Perform the export.
	 *
	 * @access public
	 * @since  1.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		$file = $this->get_file();

		@unlink( $this->file );

		echo $file;

		/**
		 * Fire action after file output.
		 *
		 * @since 1.8
		 */
		do_action( 'give_file_export_complete', $_REQUEST );

		give_die();
	}

	/**
	 * Set the properties specific to the export.
	 *
	 * @since 1.5
	 *
	 * @param array $request The Form Data passed into the batch processing.
	 */
	public function set_properties( $request ) {
	}

	/**
	 * Unset the properties specific to the export.
	 *
	 * @since 1.8.9
	 *
	 * @param array             $request The Form Data passed into the batch processing.
	 * @param Give_Batch_Export $export
	 */
	public function unset_properties( $request, $export ) {
	}

	/**
	 * Allow for pre-fetching of data for the remainder of the exporter.
	 *
	 * @access public
	 * @since  1.5
	 * @return void
	 */
	public function pre_fetch() {
	}

}
