<?php
/**
 * Customers Export Class
 *
 * This class handles customer export
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Download_History_Export Class
 *
 * @since 1.4.4
 */
class EDD_Download_History_Export extends EDD_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.4.4
	 */
	public $export_type = 'download_history';


	/**
	 * Set the export headers
	 *
	 * @access public
	 * @since 1.4.4
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
			set_time_limit( 0 );

		$month = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' );
		$year  = isset( $_POST['year']  ) ? absint( $_POST['year']  ) : date( 'Y' );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'give_download_history_export_filename', 'give-export-' . $this->export_type . '-' . $month . '-' . $year ) . '.csv' );
		header( "Expires: 0" );
	}


	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'date'     => __( 'Date',   'give' ),
			'user'     => __( 'Downloaded by', 'give' ),
			'ip'       => __( 'IP Address', 'give' ),
			'download' => __( 'Product', 'give' ),
			'file'     => __( 'File', 'give' )
		);
		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 1.4.4
 	 * @global object $give_logs EDD Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $give_logs;

		$data = array();

		$args = array(
			'nopaging' => true,
			'log_type' => 'file_download',
			'monthnum' => isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' ),
			'year'     => isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' )
		);

		$logs = $give_logs->get_connected_logs( $args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$user_info = get_post_meta( $log->ID, '_give_log_user_info', true );
				$files     = give_get_download_files( $log->post_parent );
				$file_id   = (int) get_post_meta( $log->ID, '_give_log_file_id', true );
				$file_name = isset( $files[ $file_id ]['name'] ) ? $files[ $file_id ]['name'] : null;
				$user      = get_userdata( $user_info['id'] );
				$user      = $user ? $user->user_login : $user_info['email'];

				$data[]    = array(
					'date'     => $log->post_date,
					'user'     => $user,
					'ip'       => get_post_meta( $log->ID, '_give_log_ip', true ),
					'download' => get_the_title( $log->post_parent ),
					'file'     => $file_name
				);
			}
		}

		$data = apply_filters( 'give_export_get_data', $data );
		$data = apply_filters( 'give_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}