<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Data
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Data' ) ) :

	/**
	 * Give_Settings_Data.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Data extends Give_Settings_Page {
		/**
		 * Migrations list table app container ID
		 * @since 2.10.0
		 */
		const CONTAINER_ID = 'give_migrations_table_app';

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
			$this->id          = 'data';
			$this->label       = esc_html__( 'Data', 'give' );
			$this->default_tab = 'database_updates';

			parent::__construct();

			// Do not use main form for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				add_action( 'give-tools_open_form', '__return_empty_string' );
				add_action( 'give-tools_close_form', '__return_empty_string' );
				/**
				 * Render app container
				 * @since 2.10.0
				 */
				add_action( 'give_admin_field_' . self::CONTAINER_ID, [ $this, 'render_container' ] );
			}
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			$settings        = [];
			$current_section = give_get_current_setting_section();

			switch ( $current_section ) {
				case 'give_recount_stats':
					$settings = apply_filters(
						'give_recount_stats_settings',
						[
							[
								'id'         => 'give_tools_tools',
								'type'       => 'title',
								'table_html' => false,
							],
							[
								'id'   => 'api',
								'name' => esc_html__( 'Tools', 'give' ),
								'type' => 'data',
							],
							[
								'id'         => 'give_tools_tools',
								'type'       => 'sectionend',
								'table_html' => false,
							],
						]
					);

					break;

				case 'database_updates':
					$settings = [
						[
							'id'   => self::CONTAINER_ID,
							'type' => self::CONTAINER_ID,
						],
					];

					break;
			}

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
		 * Get sections.
		 *
		 * @return array
		 *
		 * @since 2.10.0
		 */
		public function get_sections() {
			$sections = [
				'database_updates'   => __( 'Database updates', 'give' ),
				'give_recount_stats' => __( 'Recount stats', 'give' ),
			];

			$sections = apply_filters( 'give_data_views', $sections );

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}

		/**
		 * Render Migrations list table app container
		 *
		 * @since 2.10.0
		 */
		public function render_container() {
			printf( '<div id="%s" style="padding-top: 20px"></div>', self::CONTAINER_ID );
		}
	}

endif;

return new Give_Settings_Data();
