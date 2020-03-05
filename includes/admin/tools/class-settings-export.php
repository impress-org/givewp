<?php
/**
 * Give Exports Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Export
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Export' ) ) :

	/**
	 * Give_Settings_Export.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Export extends Give_Settings_Page {
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
			$this->id    = 'export';
			$this->label = __( 'Export', 'give' );

			parent::__construct();

			add_action( 'give_admin_field_tools_export', array( 'Give_Settings_Export', 'render_export_field' ), 10, 2 );

			// Do not use main donor for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				add_action( 'give-tools_open_form', '__return_empty_string' );
				add_action( 'give-tools_close_form', '__return_empty_string' );

				require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-give-export-donations.php';
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
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'give_get_settings_' . $this->id,
				array(
					array(
						'id'         => 'give_tools_export',
						'type'       => 'title',
						'table_html' => false,
					),
					array(
						'id'   => 'export',
						'name' => __( 'Export', 'give' ),
						'type' => 'tools_export',
					),
					array(
						'id'         => 'give_tools_export',
						'type'       => 'sectionend',
						'table_html' => false,
					),
				)
			);

			// Output.
			return $settings;
		}

		/**
		 * Render report export field
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public static function render_export_field( $field, $option_value ) {
			include_once 'views/html-admin-page-exports.php';
		}
	}

endif;

return new Give_Settings_Export();
