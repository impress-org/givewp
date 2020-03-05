<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Page
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Page' ) ) :

	/**
	 * Give_Settings_Page.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Page {

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
		 * Default tab.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $default_tab = '';

		/**
		 * Current setting page.
		 *
		 * @since 1.8
		 * @var   string|null
		 */
		private $current_setting_page = null;

		/**
		 * Flag to check if enable saving option for setting page or not
		 *
		 * @since 1.8.17
		 * @var bool
		 */
		protected $enable_save = true;

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Get current setting page.
			$this->current_setting_page = give_get_current_setting_page();

			// Get current section.
			$this->current_section = give_get_current_setting_section();

			add_filter( "give_default_setting_tab_section_{$this->id}", array( $this, 'set_default_setting_tab' ), 10 );
			add_filter( "{$this->current_setting_page}_tabs_array", array( $this, 'add_settings_page' ), 20 );
			add_action( "{$this->current_setting_page}_settings_{$this->id}_page", array( $this, 'output' ) );

			// Output sections.
			add_action(
				"{$this->current_setting_page}_sections_{$this->id}_page",
				array(
					$this,
					'output_sections',
				)
			);

			// Save hide button by default.
			$GLOBALS['give_hide_save_button'] = true;

			// Enable saving feature.
			if ( $this->enable_save ) {
				add_action( "{$this->current_setting_page}_save_{$this->id}", array( $this, 'save' ) );
			}
		}


		/**
		 * Get setting id
		 *
		 * @since  1.8.17
		 * @access public
		 * @return string
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Default setting tab.
		 *
		 * @since  1.8
		 *
		 * @param  $setting_tab
		 *
		 * @return string
		 */
		function set_default_setting_tab( $setting_tab ) {
			return $this->default_tab;
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
			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, array() );

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
			return apply_filters( 'give_get_sections_' . $this->id, array() );
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

			// Bailout.
			if ( empty( $sections ) ) {
				return;
			}

			// Show section settings only if setting section exist.
			if ( $current_section && ! in_array( $current_section, array_keys( $sections ), true ) ) {
				echo wp_kses_post( '<div class="error"><p>' . __( 'Oops, this settings page does not exist.', 'give' ) . '</p></div>' );
				$GLOBALS['give_hide_save_button'] = true;

				return;
			}

			if ( is_null( $this->current_setting_page ) ) {
				$this->current_setting_page = give_get_current_setting_page();
			}

			$section_list = array();
			foreach ( $sections as $id => $label ) {

				// If the `$label` return array then get title from the array as a label.
				if ( is_array( $label ) && ! empty( $label['title'] ) ) {
					$label = $label['title'];
				}

				/**
				 * Fire the filter to hide particular section on tab.
				 *
				 * @since 2.0
				 */
				if ( apply_filters( "give_hide_section_{$id}_on_{$this->id}_page", false, $sections, $this->id ) ) {
					continue;
				}

				$section_list[] = '<li><a href="' . admin_url( 'edit.php?post_type=give_forms&page=' . $this->current_setting_page . '&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . $label . '</a>';
			}

			echo wp_kses_post(
				sprintf(
					'<ul class="give-subsubsub">%s</ul><br class="clear" /><hr>',
					implode( ' | </li>', $section_list )
				)
			);
		}

		/**
		 * Output the settings.
		 *
		 * Note: if you want to overwrite this function then manage show/hide save button in your class.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output() {
			if ( $this->enable_save ) {
				$GLOBALS['give_hide_save_button'] = false;
			}

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
			$settings        = $this->get_settings();
			$current_section = give_get_current_setting_section();

			/**
			 * Use this filter if you want to implement your custom save logic.
			 *
			 * @since 2.1
			 */
			if ( apply_filters( "give_save_options_{$this->id}_{$current_section}", true ) ) {
				Give_Admin_Settings::save_fields( $settings, 'give_settings' );
			}

			/**
			 * Trigger Action
			 *
			 * @since 1.8
			 */
			do_action( 'give_update_options_' . $this->id . '_' . $current_section );
		}

		/**
		 * Get heading labels
		 *
		 * @since  1.8.7
		 * @access private
		 *
		 * @return array
		 */
		private function get_heading() {
			$heading[]       = give_get_admin_page_menu_title();
			$heading[]       = $this->label;
			$section         = $this->get_sections();
			$current_section = give_get_current_setting_section();

			if ( array_key_exists( $current_section, $section ) ) {
				$heading[] = $section[ $current_section ];
			}

			return array_unique( $heading );
		}

		/**
		 * Get heading html
		 *
		 * @since  1.8.7
		 * @access private
		 *
		 * @return string
		 */
		public function get_heading_html() {
			return sprintf(
				'<h1 class="wp-heading-inline">%s</h1>',
				implode( ' <span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> ', $this->get_heading() )
			);
		}
	}

endif;
