<?php
/**
 * Core Settings Export Class
 *
 * This class handles the export of Give's core settings
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.15
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Core_Settings_Export Class
 *
 * @since 1.8.15
 */
class Give_Core_Settings_Export extends Give_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.8.15
	 */
	public $export_type = 'core-settings';

	/**
	 * Set the export headers
	 *
	 * @access public
	 * @since  1.8.15
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'give_core_settings_export_filename', 'give-export-' . $this->export_type . '-' . date( 'n' ) . '-' . date( 'Y' ) ) . '.json' );
		header( 'Expires: 0' );
	}

	/**
	 * Prints Give's core settings in JSON format
	 *
	 * @access public
	 * @since 1.8.15
	 */
	public function json_core_settings_export() {
		echo wp_json_encode( get_option( 'give_settings' ) );
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since  1.8.15
	 */
	public function export() {
		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		// Set headers.
		$this->headers();

		$this->json_core_settings_export();

		give_die();
	}
}
