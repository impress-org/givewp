<?php
/**
 * Give Reports Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Forms_Report
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Forms_Report' ) ) :

	/**
	 * Give_Forms_Report.
	 *
	 * @sine 1.8
	 */
	class Give_Forms_Report {

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
			$this->id    = 'forms';
			$this->label = esc_html__( 'Forms', 'give' );

			add_filter( 'give-reports_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( "give-reports_settings_{$this->id}_page", array( $this, 'output' ) );
			add_action( 'give_admin_field_report_forms', array( $this, 'render_report_forms_field' ), 10, 2 );

		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8
		 * @param  array $pages List of pages.
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

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'give_get_settings_' . $this->id,
				array(
					array(
						'id'   => 'give_reports_forms',
						'type' => 'title',
						'table_html' => false
					),
					array(
						'id'   => 'forms',
						'name' => esc_html__( 'Forms', 'give' ),
						'type' => 'report_forms',
					),
					array(
						'id'   => 'give_reports_forms',
						'type' => 'sectionend',
						'table_html' => false
					)
				)
			);

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

		/**
		 * Render report forms field
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public function render_report_forms_field( $field, $option_value ) {
			do_action( 'give_reports_view_forms');
		}
	}

endif;

return new Give_Forms_Report();
