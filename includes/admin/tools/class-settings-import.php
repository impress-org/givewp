<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Import
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Import' ) ) {

	/**
	 * Give_Settings_Import.
	 *
	 * Add a submenu page in give tools menu called Import donations which import the donations from the CSV files.
	 *
	 * @since 1.8.13
	 */
	class Give_Settings_Import extends Give_Settings_Page {
		/**
		 * Flag to check if enable saving option for setting page or not
		 *
		 * @since 1.8.17
		 * @var bool
		 */
		protected $enable_save = false;

		/**
		 * Importing donation per page.
		 *
		 * @since 1.8.13
		 *
		 * @var   int
		 */
		public static $per_page = 5;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'import';
			$this->label = __( 'Import', 'give' );

			parent::__construct();

			// Will display html of the import donation.
			add_action( 'give_admin_field_tools_import', array(
				'Give_Settings_Import',
				'render_import_field',
			), 10, 2 );

			// Do not use main form for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				add_action( "give-tools_open_form", '__return_empty_string' );
				add_action( "give-tools_close_form", '__return_empty_string' );

				require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/import/class-give-import-donations.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/import/class-give-import-core-settings.php';
			}
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8.13
		 * @return array
		 */
		public function get_settings() {
			/**
			 * Filter the settings.
			 *
			 * @since  1.8.13
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'give_get_settings_' . $this->id,
				array(
					array(
						'id'         => 'give_tools_import',
						'type'       => 'title',
						'table_html' => false,
					),
					array(
						'id'   => 'import',
						'name' => __( 'Import', 'give' ),
						'type' => 'tools_import',
					),
					array(
						'name'  => esc_html__( 'Import Docs Link', 'give' ),
						'id'    => 'import_docs_link',
						'url'   => esc_url( 'http://docs.givewp.com/tools-importer' ),
						'title' => __( 'Import Tab', 'give' ),
						'type'  => 'give_docs_link',
					),
					array(
						'id'         => 'give_tools_import',
						'type'       => 'sectionend',
						'table_html' => false,
					),
				)
			);

			// Output.
			return $settings;
		}

		/**
		 * Render report import field
		 *
		 * @since  1.8.13
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public static function render_import_field( $field, $option_value ) {
			include_once GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-imports.php';
		}
	}
}
return new Give_Settings_Import();
