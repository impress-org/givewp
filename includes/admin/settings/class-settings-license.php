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
		protected $enable_save = false;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'licenses';
			$this->label = esc_html__( 'Licenses', 'give' );

			parent::__construct();

			// Do not use main form for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				// Remove default parent form.
				add_action( "give-settings_open_form", '__return_empty_string' );
				add_action( "give-settings_close_form", '__return_empty_string' );
			}
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


		/**
		 * Render  license key field
		 *
		 * @since 2.5.0
		 */
		public function output() {
			ob_start();
			?>
			<div id="give-addon-uploader-wrap" ondragover="event.preventDefault()">
				<div id="give-addon-uploader-inner">
					<?php if ( 'direct' !== get_filesystem_method() ) : ?>
						<div class="give-notice notice notice-error inline">
							<p>
								<?php
								echo sprintf(
									__( 'Sorry, you can not upload plugin from here because we do not have direct access to file system. Please <a href="%1$s" target="_blank">click here</a> to upload Give Add-on.', 'give' ),
									admin_url( 'plugin-install.php?tab=upload' )
								);
								?>
							</p>
						</div>
					<?php else: ?>
						<div class="give-notices"></div>
						<div class="give-form-wrap">
							<?php _e( '<h1>Drop files here </br>or</h1>', 'give' ); ?>
							<form method="post" enctype="multipart/form-data" class="give-upload-form" action="/">
								<?php wp_nonce_field( 'give-upload-addon', '_give_upload_addon' ); ?>
								<input type="file" name="addon" value="<?php _e( 'Select File', 'give' ); ?>">
							</form>
						</div>
						<div class="give-activate-addon-wrap" style="display: none">
							<button
								class="give-activate-addon-btn button-primary"
								data-activate="<?php _e( 'Activate Addon', 'give' ); ?>"
								data-activating="<?php _e( 'Activateing Addon...', 'give' ); ?>"
							><?php _e( 'Activate Addon', 'give' ); ?></button>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div id="give-license-activator-wrap">
				<div id="give-license-activator-inner">
					<div class="give-notices"></div>
					<form method="post" action="">
						<?php wp_nonce_field( 'give-license-activator-nonce', 'give_license_activator_nonce' ); ?>
						<label for="give-license-activator" class="screen-reader-text"><?php _e( 'Activate License', 'give' ); ?></label>
						<input id="give-license-activator" type="text" name="give_license_key" placeholder="<?php _e( 'Enter a valid license key', 'give' ) ?>">
						<input
							data-activate="<?php _e( 'Activate License', 'give' ); ?>"
							data-activating="<?php _e( 'Verifying License...', 'give' ); ?>"
							value="<?php _e( 'Activate License', 'give' ); ?>"
							type="submit"
							class="button"
							disabled
						>
					</form>
				</div>

				<p class="give-field-description"><?php _e( 'Enter a license key above to unlock your GiveWP add-ons. You can access your licenses anytime from the My Account section on the GiveWP website.' ); ?></p>
			</div>

			<h2><?php _e( 'License and Downloads', 'give' ); ?></h2>
			<?php
			$refresh_status = get_option(
				'give_licenses_refreshed_last_checked',
				array(
					'time'  => date( 'Ymd' ),
					'count' => 0,
				)
			);

			$is_allow_refresh = ( $refresh_status['time'] === date( 'Ymd' ) && 5 > $refresh_status['count'] ) || ( $refresh_status['time'] < date( 'Ymd' ) );
			$button_title = __( 'You can not refresh licenses because exceed limit. Only 5 times allowed per day', 'give' );
			?>
			<button
				id="give-button__refresh-licenses"
				class="button-secondary"
				data-activate="<?php _e( 'Refresh all licenses', 'give' ); ?>"
				data-activating="<?php _e( 'Refreshing all licenses...', 'give' ); ?>"
				data-nonce="<?php echo wp_create_nonce( 'give-refresh-all-licenses' ); ?>"
				<?php echo $is_allow_refresh ? '' : 'disabled'; ?>
				<?php echo $is_allow_refresh ? '' : sprintf( 'title="%1$s"', $button_title ); ?>
			>
				<?php _e( 'Refresh All Licenses', 'give' ); ?>
			</button>
			<section id="give-licenses-container"><?php echo Give_License::render_licenses_list(); ?></section>
			<?php
			echo ob_get_clean();
		}
	}

endif;

return new Give_Settings_License();
