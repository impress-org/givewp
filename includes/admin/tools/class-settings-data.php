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
         * @since 4.2.0 rename to GIVE_MIGRATIONS_TABLE_APP
         * @since 2.10.0
         */
		const GIVE_MIGRATIONS_TABLE_APP = 'give_migrations_table_app';

        /**
         * @since 4.2.0
         */
        const GIVE_ORPHANED_FORMS_TABLE_APP = 'give_orphaned_forms_app';

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
				 * Render migrations container
				 * @since 2.10.0
				 */
				add_action( 'give_admin_field_' . self::GIVE_MIGRATIONS_TABLE_APP, [$this, 'render_migrations_container'] );
                /**
                 * Render orphaned forms container
                 * @since 4.2.0
                 */
                if ('enabled' === give_get_option('show_orphaned_forms_table', 'disabled')) {
                    add_action( 'give_admin_field_' . self::GIVE_ORPHANED_FORMS_TABLE_APP, [$this, 'render_orphaned_forms_container'] );
                }

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
                            'id'   => self::GIVE_MIGRATIONS_TABLE_APP,
                            'type' => self::GIVE_MIGRATIONS_TABLE_APP,
						],
					];

					break;


                case 'orphaned_forms':
                    $settings = [
                        [
                            'id'   => self::GIVE_ORPHANED_FORMS_TABLE_APP,
                            'type' => self::GIVE_ORPHANED_FORMS_TABLE_APP,
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

            if ('enabled' === give_get_option('show_orphaned_forms_table', 'disabled')) {
                $sections['orphaned_forms'] = __( 'Orphaned donation forms', 'give' );
            }

			$sections = apply_filters( 'give_data_views', $sections );

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}

        /**
         * Render Migrations list table app container
         *
         * @since 4.2.0 renamed to render_migrations_container
         * @since 2.10.0
         */
		public function render_migrations_container() {
			printf( '<div id="%s" style="padding-top: 20px"></div>', self::GIVE_MIGRATIONS_TABLE_APP );
		}

        /**
         * Render orphaned forms container
         *
         * @since 4.2.0
         */
        public function render_orphaned_forms_container() {
            printf( '<div id="%s" style="padding-top: 20px"></div>', self::GIVE_ORPHANED_FORMS_TABLE_APP );
        }
	}

endif;

return new Give_Settings_Data();
