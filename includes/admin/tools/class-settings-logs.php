<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Logs
 * @copyright   Copyright (c) 2016, WordImpress
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
		 * Setting page id.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $label = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'logs';
			$this->label = esc_html__( 'Logs', 'give' );

			$this->default_tab = 'sales';

			parent::__construct();

			// Do not use main form for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				add_action( 'give-tools_open_form', '__return_empty_string' );
				add_action( 'give-tools_close_form', '__return_empty_string' );
			}
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8
		 *
		 * @param  array $pages Lst of pages.
		 *
		 * @return array
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			// Hide save button.
			$GLOBALS['give_hide_save_button'] = true;

			// Get settings.
			$settings = apply_filters( 'give_settings_logs', array(
				array(
					'id'         => 'give_tools_logs',
					'type'       => 'title',
					'table_html' => false,
				),
				array(
					'id'   => 'api',
					'name' => esc_html__( 'Log', 'give' ),
					'type' => 'logs',

				),
				array(
					'id'         => 'give_tools_logs',
					'type'       => 'sectionend',
					'table_html' => false,
				),
			) );

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Get sections.
		 *
		 * @since 1.8
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'sales'          => esc_html__( 'Donations', 'give' ),
				'gateway_errors' => esc_html__( 'Payment Errors', 'give' ),
				'api_requests'   => esc_html__( 'API Requests', 'give' ),
			);

			$sections = apply_filters( 'give_log_views', $sections );

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output() {
			$settings = $this->get_settings();

			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}
	}

endif;

return new Give_Settings_Logs();
