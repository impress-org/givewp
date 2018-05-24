<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_License
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_License' ) ) :

	/**
	 * Give_Settings_License.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_License extends Give_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'licenses';
			$this->label = esc_html__( 'Licenses', 'give' );

			parent::__construct();

			// Filter to remove the license tab.
			add_filter( 'give-settings_tabs_array', array( $this, 'remove_license_tab' ), 9999999, 1 );

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
			 * Filter the licenses settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'give_settings_licenses', $settings );

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Remove the license tab if no Give addon
		 * is activated.
		 *
		 * @param array $tabs Give Settings Tabs.
		 *
		 * @since 2.1.4
		 *
		 * @return array
		 */
		public function remove_license_tab( $tabs ) {

			/**
			 * Remove the license tab if no Give addon
			 * is activated.
			 */
			if ( ! give_any_give_addon_activated() ) {
				unset( $tabs['licenses'] );
			}

			return $tabs;
		}
	}

endif;

return new Give_Settings_License();
