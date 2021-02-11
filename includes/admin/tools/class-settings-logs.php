<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Logs
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Logs' ) ) :

	/**
	 * Give_Settings_Logs.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Logs extends Give_Settings_Page {
		/**
		 * Flag to check if enable saving option for setting page or not
		 *
		 * @since 1.8.17
		 * @var bool
		 */
		protected $enable_save = false;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'logs';
			$this->label = __( 'Logs', 'give' );
			parent::__construct();

			// Do not use main form for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				add_action( 'give-tools_open_form', '__return_empty_string' );
				add_action( 'give-tools_close_form', '__return_empty_string' );
			}

		}

		/**
		 * Logs list table app container
		 *
		 * @since 2.10.0
		 */
		public function output() {
			echo '<div id="give-logs-list-table-app" style="padding-top: 20px;"></div>';
		}
	}

endif;

return new Give_Settings_Logs();
