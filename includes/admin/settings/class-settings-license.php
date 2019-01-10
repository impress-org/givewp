<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_License
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
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
			 * Remove the license tab if no Give licensed addon
			 * is activated.
			 */
			if ( ! $this->is_show_setting_page() ) {
				unset( $tabs['licenses'] );
			}

			return $tabs;
		}

		/**
		 * Returns if at least one Give addon is activated.
		 * Note: note only for internal logic
		 *
		 * @since 2.1.4
		 * @access private
		 *
		 * @return bool
		 */
		private function is_show_setting_page() {
			$licensed_addons   = Give_License::get_licensed_addons();
			$activated_plugins = get_option( 'active_plugins', array() );

			// Get list of network enabled plugin.
			if ( is_multisite() ) {
				$sitewide_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
				$activated_plugins = ! empty( $activated_plugins )
					? array_merge( $sitewide_activated_plugins, $activated_plugins )
					: $sitewide_activated_plugins;
			}

			return (bool) count( array_intersect( $activated_plugins, $licensed_addons ) );
		}
	}

endif;

return new Give_Settings_License();
