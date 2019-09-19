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
				add_action( 'give-settings_open_form', '__return_empty_string' );
				add_action( 'give-settings_close_form', '__return_empty_string' );

				// Refresh licenses when visit license setting page.
				give_refresh_licenses();
			}
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 * @since  1.8
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
			 * @param array $settings
			 *
			 * @since  1.8
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Render  license key field
		 *
		 * @since 2.5.0
		 */
		public function output() {
			ob_start();
			?>
			<div class="give-license-settings-wrap">

				<div class="give-grid-row">

					<div class="give-grid-col-6">
						<div id="give-license-activator-wrap" class="give-license-top-widget">
							<div id="give-license-activator-inner">

								<h2 class="give-license-widget-heading">
									<span class="dashicons dashicons-plugins-checked"></span>
									<?php _e( 'Activate an Add-on License', 'give' ); ?>
								</h2>

								<p class="give-field-description">
									<?php
									printf(
										__( 'Enter your license key below to unlock your GiveWP add-ons. You can access your licenses anytime from the <a href="%1$s" target="_blank">My Account</a> section on the GiveWP website. ', 'give' ),
										Give_License::get_account_url()
									);
									?>
								</p>

								<form method="post" action="" class="give-license-activation-form">

									<div class="give-license-notices"></div>

									<?php wp_nonce_field( 'give-license-activator-nonce', 'give_license_activator_nonce' ); ?>

									<label
										for="give-license-activator"
										class="screen-reader-text">
										<?php _e( 'Activate License', 'give' ); ?>
									</label>

									<input
										id="give-license-activator"
										type="text"
										name="give_license_key"
										placeholder="<?php _e( 'Enter your license key', 'give' ); ?>"
									/>

									<input
										data-activate="<?php _e( 'Activate License', 'give' ); ?>"
										data-activating="<?php _e( 'Verifying License...', 'give' ); ?>"
										value="<?php _e( 'Activate License', 'give' ); ?>"
										type="submit"
										class="button button-primary"
									/>

								</form>

							</div>
						</div>
					</div><!-- /.give-grid-col-6 -->

					<div class="give-grid-col-6">
						<div id="give-addon-uploader-wrap" class="give-license-top-widget"
						     ondragover="event.preventDefault()">
							<div id="give-addon-uploader-inner">
								<h2 class="give-license-widget-heading">
									<span class="dashicons dashicons-upload"></span>
									<?php _e( 'Upload and Activate an Add-on', 'give' ); ?>
								</h2>

								<?php if( ! is_multisite() ) :  ?>

									<p class="give-field-description">
										<?php
										printf(
											__( 'Drag an add-on zip file below to upload and activate it. Access your downloads by activating a license or via the <a href="%1$s" target="_blank">My Downloads</a> section on the GiveWP website. ', 'give' ),
											Give_License::get_downloads_url()
										);
										?>
									</p>

									<?php if ( 'direct' !== get_filesystem_method() ) : ?>
										<div class="give-notice notice notice-error inline">
											<p>
												<?php
												echo sprintf(
													__( 'Sorry, you can not upload plugin from here because we do not have direct access to file system. Please <a href="%1$s" target="_blank">click here</a> to upload GiveWP Add-on.', 'give' ),
													admin_url( 'plugin-install.php?tab=upload' )
												);
												?>
											</p>
										</div>
									<?php else : ?>
										<div class="give-upload-addon-form-wrap">
											<form
												method="post"
												enctype="multipart/form-data"
												class="give-upload-addon-form"
												action="/">

												<div class="give-addon-upload-notices"></div>

												<div class="give-activate-addon-wrap">
													<p><span
															class="dashicons dashicons-yes"></span> <?php _e( 'Add-on succesfully uploaded.', 'give' ); ?>
													</p>
													<button
														class="give-activate-addon-btn button-primary"
														data-activate="<?php _e( 'Activate Add-on', 'give' ); ?>"
														data-activating="<?php _e( 'Activating Add-on...', 'give' ); ?>"
													><?php _e( 'Activate Add-on', 'give' ); ?></button>
												</div>

												<?php wp_nonce_field( 'give-upload-addon', '_give_upload_addon' ); ?>

												<p class="give-upload-addon-instructions">
													<?php _e( 'Drag a plugin zip file here to upload', 'give' ); ?><br>
													<span><?php _e( 'or', 'give' ); ?></span>
												</p>

												<label for="give-upload-addon-file-select" class="button button-small">
													<?php _e( 'Select a File', 'give' ); ?>
												</label>

												<input
													id="give-upload-addon-file-select"
													type="file"
													name="addon"
													value="<?php _e( 'Select File', 'give' ); ?>"
												/>

											</form>
										</div>
									<?php endif; ?>
									<?php else:
									printf(
										__( 'Because of security reasons you can not upload add-ons from here. Please <a href="%1$s" target="_blank">visit network plugin install page</a> to install add-ons.' ),
										network_admin_url( 'plugin-install.php' )
									);
									?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>

				<div class="give-grid-row<?php echo get_option( 'give_licenses', array() ) ? '' : ' give-hidden' ?>">
					<div class="give-grid-col-12">

						<div class="give-licenses-list-header give-clearfix">
							<h2><?php _e( 'Licenses and Add-ons', 'give' ); ?></h2>

							<?php
							$refresh_status   = Give_License::refresh_license_status();
							$is_allow_refresh = ( $refresh_status['compare'] === date( 'Ymd' ) && 5 > $refresh_status['count'] ) || ( $refresh_status['compare'] < date( 'Ymd' ) );
							$button_title     = __( 'Refresh limit reached. Licenses can only be refreshed 5 times per day.', 'give' );
							$local_date       = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $refresh_status['time'] ) ) );
							?>

							<div id="give-refresh-button-wrap">
								<button id="give-button__refresh-licenses"
								        class="button-secondary"
								        data-activate="<?php _e( 'Refresh All Licenses', 'give' ); ?>"
								        data-activating="<?php _e( 'Refreshing All Licenses...', 'give' ); ?>"
								        data-nonce="<?php echo wp_create_nonce( 'give-refresh-all-licenses' ); ?>"
									<?php echo $is_allow_refresh ? '' : 'disabled'; ?>
									<?php echo $is_allow_refresh ? '' : sprintf( 'title="%1$s"', $button_title ); ?>>
									<?php _e( 'Refresh All Licenses', 'give' ); ?>
								</button>
								<span id="give-last-refresh-notice">
									<?php echo sprintf(
										__( 'Last refreshed on %1$s at %2$s', 'give' ),
										date( give_date_format(), $local_date ),
										date( 'g:i a', $local_date )
									); ?>
									</span>
							</div>

							<hr>
							<p class="give-field-description"><?php _e('The following list displays your add-ons and their corresponding activation and license statuses.', 'give'); ?></p>

						</div>

						<section id="give-licenses-container">
							<?php echo Give_License::render_licenses_list(); ?>
						</section>

					</div>
				</div>
			</div>

			<?php
			echo ob_get_clean();
		}
	}

endif;

return new Give_Settings_License();
