<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Addon
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Addon' ) ) :

	/**
	 * Give_Settings_Addon.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Addon extends Give_Settings_Page {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'addons';
			$this->label = esc_html__( 'Add-ons', 'give' );

			parent::__construct();
		}

		/**
		 * Default setting tab.
		 *
		 * @since  1.8
		 * @param  $setting_tab
		 * @return string
		 */
		function set_default_setting_tab( $setting_tab ) {
			$default_tab = '';

			// Set default tab to first setting tab.
			if( $sections = array_keys( $this->get_sections() ) ) {
				$default_tab = current( $sections );
			}
			return $default_tab;
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8
		 * @param  array $pages Lst of pages.
		 * @return array
		 */
		public function add_settings_page( $pages ) {
			$sections = $this->get_sections();

			// Bailout: Do not add addons setting tab if it does not contain any setting fields.
			if( ! empty( $sections ) ) {
				$pages[ $this->id ] = $this->label;
			}

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

			/**
			 * Filter the addons settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'give_settings_addons', $settings );

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
	}

endif;

return new Give_Settings_Addon();
