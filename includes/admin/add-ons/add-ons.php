<?php
/**
 * Admin Add-ons
 *
 * @package     Give
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class Give_Admin
 */
class Give_Addons {
	/**
	 * Instance.
	 *
	 * @since  2.5.0
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.5.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @return Give_Addons
	 * @since  2.5.0
	 * @access public
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup Admin
	 *
	 * @sinve  2.5.0
	 * @access private
	 */
	private function setup() {

	}

}

Give_Addons::get_instance();


/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @return void
 * @since 1.0
 */
function give_add_ons_page() {
	// @todo: show plugin activate button if plugin uploaded successfully.
	?>
	<div class="wrap" id="give-add-ons">
		<h1><?php echo esc_html( get_admin_page_title() ); ?>
			&nbsp;&mdash;&nbsp;<a href="https://givewp.com/addons/" class="button-primary give-view-addons-all"
			                      target="_blank"><?php esc_html_e( 'View All Add-ons', 'give' ); ?>
				<span class="dashicons dashicons-external"></span></a>
		</h1>

		<hr class="wp-header-end">

		<p><?php esc_html_e( 'The following Add-ons extend the functionality of Give.', 'give' ); ?></p>

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
				<form method="post">
					<?php wp_nonce_field( 'give-license-activator-nonce', 'give_license_activator_nonce' ); ?>
					<label for="give-license-activator"><?php _e( 'Activate License', 'give' ); ?></label>
					<input id="give-license-activator" type="text" name="give_license_key" placeholder="<?php _e( 'Enter a valid license key', 'give' ) ?>">
					<input
						data-activate="<?php _e( 'Activate License', 'give' ); ?>"
						data-activating="<?php _e( 'Verifying License...', 'give' ); ?>"
						value="<?php _e( 'Activate License', 'give' ); ?>"
						type="submit"
						class="button"
					>
				</form>
			</div>

			<p class="give-field-description"><?php _e( 'Enter a license key above to unlock your GiveWP add-ons. You can access your licenses anytime from the My Account section on the GiveWP website.' ); ?></p>
		</div>

		<h2><?php _e( 'License and Downloads', 'give' ); ?></h2>
		<?php $give_plugins = give_get_plugins(); ?>

		<?php foreach ( $give_plugins as $give_plugin ): ?>
			<?php
			if ( 'add-on' !== $give_plugin['Type'] ) {
				continue;
			}

			$addon_shortname      = Give_License::get_short_name( $give_plugin['Name'] );
			$addon_slug           = str_replace( '_', '-', $addon_shortname );
			$addon_license_active = __give_get_active_license_info( Give_License::get_short_name( $give_plugin['Name'] ) );
			$addon_license_key    = give_get_option( "{$addon_shortname}_license_key" );
			?>
			<div class="give-addon-wrap">
				<div class="give-addon-inner">
					<div class="give-row">
						<div class="give-left">
							<span class="give-license__key give-background__gray give-border">
								<?php if ( $give_plugin['License'] ): ?>
									<?php echo str_repeat( '*' , strlen( $addon_license_key ) - 5 ) . substr( $addon_license_key, -5, 5 ) ; ?>
								<?php endif; ?>
							</span>

							<?php //@todo: handle all license status; ?>
							<span class="give-text">
								<?php if ( ! $give_plugin['License'] ) : ?>
									<i class="dashicons dashicons-warning"></i>
									<?php _e( 'Unlicensed', 'give' ); ?>
								<?php elseif ( 'valid' === $addon_license_active->license ): ?>
									<i class="dashicons dashicons-yes give-license__status"></i>
									<?php _e( 'Active', 'give' ); ?>
								<?php else: ?>
									<i class="dashicons dashicons-yes give-license__status"></i>
									<?php _e( 'License', 'give' ); ?>
								<?php endif; ?>
							</span>

							<?php //@todo: handle all license status; ?>
							<span class="give-text">
								<?php if ( ! $give_plugin['License'] ) : ?>
									<?php echo sprintf( '<a href="%1$s">%2$s</a>', '#', __( 'Purchase license', 'give' ) ); ?>
								<?php elseif ( 'valid' === $addon_license_active->license ): ?>
									<?php echo sprintf( '%1$s %2$s', $addon_license_active->activations_left, __( 'activations remaining', 'give' ) ); ?>
								<?php elseif ( 'expired' === $addon_license_active->license ) : ?>
									<?php echo sprintf( '<a href="%1$s">%2$s</a>', '#', __( 'Renew to manage sites', 'give' ) ); ?>
								<?php endif; ?>
						</span>
						</div>
						<div class="give-right">
							<?php if ( ! $give_plugin['License'] ) : ?>
								<span class="give-text"><?php _e( 'Not receiving updates or support' ) ?></span>
								<span><a class="give-button button-secondary" href="#"><?php _e( 'Purchase License' ) ?></a></span>
							<?php elseif ( 'valid' === $addon_license_active->license ): ?>
								<?php echo sprintf( '%1$s %2$s', __( 'Renew:' ), date( give_date_format(), strtotime( $addon_license_active->expires ) ) ); ?>
							<?php else: ?>
								<i class="dashicons dashicons-yes give-license__status"></i>
								<?php _e( 'License', 'give' ); ?>
							<?php endif; ?>
						</div>
					</div>
					<div class="give-row give-border give-last">
						<div class="give-left">
							<span class="give-text give-plugin__name"><?php echo $give_plugin['Name']; ?></span>
							<span class="give-text">
							<?php
							echo sprintf(
								'<a href="%1$s" target="_blank">%2$s</a>',
								give_get_addon_readme_url( $addon_slug ),
								__( 'changelog', 'give' )
							);
							?>
						</span>
						</div>
						<div class="give-right">
							<span class="give-text"><?php echo sprintf( '%1$s %2$s', __( 'Version' ), $give_plugin['Version'] ) ?></span>
							<span class="give-background__gray give-border give-text give-text_small give-plugin__status"><?php _e( 'currently installed' ) ?></span>
							<span>
							<a class="give-button button-secondary" href="#" disabled="">
								<i class="dashicons dashicons-download"></i>
								<?php _e( 'Download' ) ?>
							</a>
						</span>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<?php //give_add_ons_feed(); ?>
	</div>
	<?php

}

/**
 * Add-ons Render Feed
 *
 * Renders the add-ons page feed.
 *
 * @return void
 * @since 1.0
 */
function give_add_ons_feed() {

	$addons_debug = false; // set to true to debug
	$cache        = Give_Cache::get( 'give_add_ons_feed', true );

	if ( false === $cache || ( true === $addons_debug && true === WP_DEBUG ) ) {
		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$feed = vip_safe_wp_remote_get( 'https://givewp.com/downloads/feed/', false, 3, 1, 20, array( 'sslverify' => false ) );
		} else {
			$feed = wp_remote_get( 'https://givewp.com/downloads/feed/', array( 'sslverify' => false ) );
		}

		if ( ! is_wp_error( $feed ) && ! empty( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				Give_Cache::set( 'give_add_ons_feed', $cache, HOUR_IN_SECONDS, true );
			}
		} else {
			$cache = sprintf(
				'<div class="error"><p>%s</p></div>',
				esc_html__( 'There was an error retrieving the Give Add-ons list from the server. Please try again later.', 'give' )
			);
		}
	}

	echo wp_kses_post( $cache );
}
