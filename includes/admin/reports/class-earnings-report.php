<?php
/**
 * Give Reports Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Earnings_Report
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Earnings_Report' ) ) :

	/**
	 * Give_Earnings_Report.
	 *
	 * @sine 1.8
	 */
	class Give_Earnings_Report extends Give_Settings_Page {

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
			$this->id    = 'earnings';
			$this->label = esc_html__( 'Income', 'give' );

			parent::__construct();

			add_action( 'give_admin_field_report_earnings', array( $this, 'render_report_earnings_field' ), 10, 2 );

			// Do not use main form for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				add_action( 'give-reports_open_form', '__return_empty_string' );
				add_action( 'give-reports_close_form', '__return_empty_string' );
			}
		}


		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'give_get_settings_' . $this->id,
				array(
					array(
						'id'         => 'give_tools_earnings',
						'type'       => 'title',
						'table_html' => false,
					),
					array(
						'id'   => 'earnings',
						'name' => esc_html__( 'Income', 'give' ),
						'type' => 'report_earnings',
					),
					array(
						'id'         => 'give_tools_earnings',
						'type'       => 'sectionend',
						'table_html' => false,
					),
				)
			);

			// Output.
			return $settings;
		}

		/**
		 * Render earning field
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public function render_report_earnings_field( $field, $option_value ) {
			do_action( 'give_reports_view_earnings' );
		}
	}

endif;

return new Give_Earnings_Report();
