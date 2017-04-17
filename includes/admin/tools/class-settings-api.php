<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_API
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_API' ) ) :

	/**
	 * Give_Settings_API.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_API {

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
			$this->id    = 'api';
			$this->label = esc_html__( 'API', 'give' );

			add_filter( 'give-tools_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( "give-tools_settings_{$this->id}_page", array( $this, 'output' ) );
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8
		 * @param  array $pages Lst of pages.
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
			$settings = apply_filters( 'give_settings_api', array(
				array(
					'id'   => 'give_tools_api',
					'type' => 'title',
					'table_html' => false
				),
				array(
					'id'   => 'api',
					'name' => esc_html__( 'API', 'give' ),
					'type' => 'api',
				),
				array(
					'id'   => 'give_tools_api',
					'type' => 'sectionend',
					'table_html' => false
				)
			));

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 * @param  array $settings
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
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

return new Give_Settings_API();
