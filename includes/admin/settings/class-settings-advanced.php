<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Advanced
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Advanced' ) ) :

	/**
	 * Give_Settings_Advanced.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Advanced {

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
			$this->id    = 'advanced';
			$this->label = esc_html__( 'Advanced', 'give' );

			add_filter( "give_default_setting_tab_section_{$this->id}", array( $this, 'set_default_setting_tab' ), 10 );
			add_filter( 'give-settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( "give-settings_sections_{$this->id}_page", array( $this, 'output_sections' ) );
			add_action( "give-settings_settings_{$this->id}_page", array( $this, 'output' ) );
			add_action( "give-settings_save_{$this->id}", array( $this, 'save' ) );
		}

		/**
		 * Default setting tab.
		 *
		 * @since  1.8
		 * @param  $setting_tab
		 * @return string
		 */
		function set_default_setting_tab( $setting_tab ) {
			return 'advanced-options';
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
			$settings = array();

			$current_section = give_get_current_setting_section();

			switch ( $current_section ) {
				case 'advanced-options':
					$settings = array(
						array(
							'id'   => 'give_title_data_control_2',
							'type' => 'title'
						),
						array(
							'name' => esc_html__( 'Remove All Data on Uninstall?', 'give' ),
							'desc' => esc_html__( 'When the plugin is deleted, completely remove all Give data.', 'give' ),
							'id'   => 'uninstall_on_delete',
							'type' => 'checkbox'
						),
						array(
							/* translators: %s: the_content */
							'name' => sprintf( __( 'Disable %s filter', 'give' ), '<code>the_content</code>' ),
							/* translators: 1: https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content 2: the_content */
							'desc' => sprintf( __( 'If you are seeing extra social buttons, related posts, or other unwanted elements appearing within your forms then you can disable WordPress\' content filter. <a href="%1$s" target="_blank">Learn more</a> about %2$s filter.', 'give' ), esc_url( 'https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content' ), '<code>the_content</code>' ),
							'id'   => 'disable_the_content_filter',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Load Scripts in Footer?', 'give' ),
							'desc' => esc_html__( 'Check this box if you would like Give to load all frontend JavaScript files in the footer.', 'give' ),
							'id'   => 'scripts_footer',
							'type' => 'checkbox'
						),
						array(
							'id'   => 'give_title_data_control_2',
							'type' => 'sectionend'
						)
					);
					break;
			}


			/**
			 * Filter the advanced settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'give_settings_advanced', $settings );

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
		 * @since 1.8
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'advanced-options' => esc_html__( 'Advanced Options', 'give' )
			);

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output sections.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output_sections() {
			// Get current section.
			$current_section = give_get_current_setting_section();

			// Get all sections.
			$sections = $this->get_sections();

			if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
				return;
			}

			echo '<ul class="subsubsub">';

			// Get section keys.
			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}

			echo '</ul><br class="clear" />';
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
		 * Save settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function save() {
			$settings = $this->get_settings();

			Give_Admin_Settings::save_fields( $settings, 'give_settings' );
		}
	}

endif;

return new Give_Settings_Advanced();
