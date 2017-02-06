<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Page
 * @copyright   Copyright (c) 2016, WordImpress
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
		 * Constructor.
		 */
		public function __construct() {
			// Get current setting page.
			$this->current_setting_page = give_get_current_setting_page();

			add_filter( "give_default_setting_tab_section_{$this->id}", array( $this, 'set_default_setting_tab' ), 10 );
			add_filter( "{$this->current_setting_page}_tabs_array", array( $this, 'add_settings_page' ), 20 );
			add_action( "{$this->current_setting_page}_sections_{$this->id}_page", array( $this, 'output_sections' ) );
			add_action( "{$this->current_setting_page}_settings_{$this->id}_page", array( $this, 'output' ) );
			add_action( "{$this->current_setting_page}_save_{$this->id}", array( $this, 'save' ) );
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

			// Show section settings only if setting section exist.
			if ( $current_section && ! in_array( $current_section, array_keys( $sections ) ) ) {
				echo '<div class="error"><p>' . __( 'Oops, this settings page does not exist.', 'give' ) . '</p></div>';
				$GLOBALS['give_hide_save_button'] = true;

				return;
			}

			// Bailout.
			if ( empty( $sections ) ) {
				return;
			}

			if ( is_null( $this->current_setting_page ) ) {
				$this->current_setting_page = give_get_current_setting_page();
			}

			echo '<ul class="subsubsub">';

			// Get section keys.
			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'edit.php?post_type=give_forms&page=' . $this->current_setting_page . '&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}

			echo '</ul><br class="clear" /><hr>';
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
			$settings        = $this->get_settings();
			$current_section = give_get_current_setting_section();

			Give_Admin_Settings::save_fields( $settings, 'give_settings' );

			/**
			 * Trigger Action
			 *
			 * @since 1.8
			 */
			do_action( 'give_update_options_' . $this->id . '_' . $current_section );
		}
	}

endif;
