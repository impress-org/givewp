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


	/**
	 * Render license seciton
	 *
	 * @return string
	 * @since 2.5.0
	 *
	 */
	public static function render_license_section() {
		$give_plugins  = give_get_plugins();
		$give_licenses = get_option( 'give_licenses', array() );

		// Get all access pass licenses
		$all_access_pass_licenses   = array();
		$all_access_pass_addon_list = array();
		foreach ( $give_licenses as $key => $give_license ) {
			if ( $give_license['is_all_access_pass'] ) {
				$all_access_pass_licenses[ $key ] = $give_license;

				foreach ( $give_license['download'] as $download ) {
					$all_access_pass_addon_list[] = $download['plugin_slug'];
				}
			}
		}

		$html = array(
			'unlicensed'          => '',
			'licensed'            => '',
			'all_access_licensed' => '',
		);

		foreach ( $give_plugins as $give_plugin ) {
			if (
				'add-on' !== $give_plugin['Type']
				|| false === strpos( $give_plugin['PluginURI'], 'givewp.com' )
			) {
				continue;
			}

			/* @var  stdClass $addon_license */
			$addon_shortname   = Give_License::get_short_name( $give_plugin['Name'] );
			$addon_slug        = str_replace( '_', '-', $addon_shortname );
			$item_name         = str_replace( 'give-', '', $addon_slug );
			$addon_license_key = Give_License::get_license_by_item_name( $item_name );

			if ( in_array( $give_plugin['Dir'], $all_access_pass_addon_list ) ) {
				continue;
			}

			$html_arr_key = 'unlicensed';

			if ( $addon_license_key ) {
				$html_arr_key = 'licensed';
			}

			$html["{$html_arr_key}"] .= self::get_instance()->html_by_plugin( $give_plugin );
		}

		if ( ! empty( $all_access_pass_licenses ) ) {
			foreach ( $all_access_pass_licenses as $key => $all_access_pass_license ) {
				$html['all_access_licensed'] .= self::get_instance()->html_by_license( $all_access_pass_license );
			}
		}

		return implode( '', $html );
	}

	/**
	 * Get add-on item html
	 * Note: only for internal use
	 *
	 * @param $plugin
	 *
	 * @return string
	 * @since 2.5.0
	 *
	 */
	public static function html_by_plugin( $plugin ) {
		// Bailout.
		if ( empty( $plugin ) ) {
			return '';
		}

		ob_start();
		$addon_shortname = Give_License::get_short_name( $plugin['Name'] );
		$addon_slug      = str_replace( '_', '-', $addon_shortname );
		$item_name       = str_replace( 'give-', '', $addon_slug );
		$license         = Give_License::get_license_by_item_name( $item_name );

		$default_plugin = array(
			'ChangeLogSlug' => $addon_slug,
			'DownloadURL'   => '',
		);

		if ( false !== strpos( $default_plugin['ChangeLogSlug'], '-gateway' ) ) {
			// We found that each gateway addon does not have `-gateway` in changelog file slug
			$default_plugin['ChangeLogSlug'] = str_replace( '-gateway', '', $default_plugin['ChangeLogSlug'] );
		}

		if ( $license ) {
			$license['renew_url'] = "https://givewp.com/checkout/?edd_license_key={$license['license_key']}";

			// Backward compatibility.
			if ( ! empty( $license['subscription'] ) ) {
				$license['expires']            = $license['subscription']['expires'];
				$default_plugin['DownloadURL'] = $license['download'];

				$license['renew_url'] = "https://givewp.com/checkout/?edd_license_key={$license['subscription']['subscription_key']}";
			}
		}

		$plugin['License'] = $license = wp_parse_args( $license, array(
			'item_name' => $item_name,
		) );

		$plugin = wp_parse_args( $plugin, $default_plugin )
		?>
		<div class="give-addon-wrap">
			<div class="give-addon-inner">
				<?php echo self::get_instance()->html_license_row( $license ); ?>
				<?php echo self::get_instance()->html_plugin_row( $plugin ); ?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get add-on item html
	 * Note: only for internal use
	 *
	 * @param array $license
	 *
	 * @return string
	 * @since 2.5.0
	 *
	 */
	private function html_by_license( $license ) {
		ob_start();

		$license['renew_url'] = "https://givewp.com/checkout/?edd_license_key={$license['license_key']}";
		?>
		<div class="give-addon-wrap">
			<div class="give-addon-inner">
				<?php
				echo self::get_instance()->html_license_row( $license );

				foreach ( $license['download'] as $addon ) {
					$item_name = str_replace( ' ', '-', strtolower( $addon['name'] ) );

					$default_plugin = array(
						'Name'          => $addon['name'],
						// We found that each gateway addon does not have `-gateway` in changelog file slug
						'ChangeLogSlug' => str_replace( '-gateway', '', "give-{$item_name}" ),
						'Version'       => $addon['current_version'],
						'Status'        => 'not installed',
						'DownloadURL'   => $addon['file'],
					);

					$plugin = wp_parse_args(
						self::get_plugin_by_item_name( $item_name ),
						$default_plugin
					);

					$plugin['Name'] = false === strpos( $plugin['Name'], 'Give -' )
						? $plugin['Name']
						: "Give  - {$addon['name']}";

					$plugin['License'] = $license;

					echo self::html_plugin_row( $plugin );
				}
				?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}


	/**
	 * license row html
	 *
	 * @param array $license
	 * @param array $plugin
	 *
	 * @return string
	 * @since 2.5.0
	 */
	private function html_license_row( $license, $plugin = array() ) {
		ob_start();

		$is_license         = $license && ! empty( $license['license_key'] );
		$license_key        = $is_license ? $license['license_key'] : '';
		$expires_timestamp  = $is_license ? strtotime( $license['expires'] ) : '';
		$is_license_expired = $is_license && ( 'expired' === $license['license'] || $expires_timestamp < current_time( 'timestamp', 1 ) );
		?>
		<div class="give-row">
			<div class="give-left">
					<span class="give-license__key<?php echo $license_key ? ' give-has-license-key' : ''; ?>">
						<?php $value = $license_key ? give_hide_char( $license['license_key'], 5 ) : ''; ?>
						<input type="text" value="<?php echo $value; ?>"<?php echo $value ? ' readonly' : ''; ?>>
						<?php if ( ! $license_key ) : ?>
							&nbsp;&nbsp;
							<button class="give-button__license-activate button-secondary" data-item-name="<?php echo $license['item_name']; ?>" disabled><?php _e( 'Activate License' ); ?></button>
						<?php endif; ?>
					</span>

				<?php //@todo: handle all license status;
				?>
				<?php
				if ( $license_key ) {
					echo sprintf(
						'<span class="give-text"><i class="dashicons dashicons-%2$s give-license__status"></i>&nbsp;%1$s</span>',
						$is_license_expired
							? __( 'Expired', 'give' )
							: __( 'Active', 'give' ),
						$is_license_expired
							? 'no'
							: 'yes'
					);

					if ( $is_license_expired ) {
						// @todo: need to test renew license link
						echo sprintf(
							'<span class="give-text"><a href="%1$s" target="_blank">%2$s</a></span>',
							$license['renew_url'],
							__( 'Renew to manage sites', 'give' )
						);
					} elseif ( $license_key ) {
						if ( ! $license['activations_left'] ) {
							echo sprintf(
								'<span class="give-text give-license__activation-left">%1$s</span>',
								__( 'No activation remaining', 'give' )
							);
						} else {
							echo sprintf(
								'<span class="give-text give-license__activation-left"><i class="give-background__gray">%1$s</i> %2$s</span>',
								$license['activations_left'],
								_n( 'activation remaining', 'activations remaining', $license['activations_left'], 'give' )
							);
						}
					}

					if ( ! $is_license_expired ) {
						echo sprintf(
							'<span class="give-text"><a href="http://staging.givewp.com/purchase-history/?license_id=%3$s&action=manage_licenses&payment_id=%4$s" target="_blank">%1$s</a> | <a href="javascript:void(0)" target="_blank" class="give-license__deactivate" data-license-key="%5$s" data-item-name= "%6$s" data-nonce="%7$s">%2$s</a> </span>',
							// demo url: http://staging.givewp.com/purchase-history/?license_id=175279&action=manage_licenses&payment_id=355748
							__( 'Visit site', 'give' ),
							__( 'Deactivate', 'give' ),
							$license['license_id'],
							$license['payment_id'],
							$license['license_key'],
							$license['item_name'],
							wp_create_nonce( "give-deactivate-license-{$license['item_name']}" )
						);
					}
				}
				?>
			</div>
			<div class="give-right">
				<?php if ( ! $license_key ) : ?>
					<span class="give-text"><?php _e( 'Not receiving updates or support' ) ?></span>
					<span>
						<?php
						// @todo: confirm do we need to redirect user to addon page or direct to cart with current addon.
						// help: https://docs.easydigitaldownloads.com/article/268-creating-custom-add-to-cart-links
						echo sprintf(
							'<a class="give-button button-secondary" href="%1$s" target="_blank">%2$s</a>',
							'https://givewp.com/addons/' . $license['item_name'] . '/',
							__( 'Purchase license', 'give' )
						);
						?>
					</span>
				<?php else: ?>
					<?php
					echo sprintf(
						'<span><strong>%1$s %2$s</strong></span>',
						$is_license_expired ? __( 'Expired:' ) : __( 'Renew:' ),
						date( give_date_format(), $expires_timestamp )
					);
					?>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}


	/**
	 * Plugin row html
	 *
	 * @param array $plugin
	 *
	 *
	 * @return string
	 * @since 2.5.0
	 */
	public static function html_plugin_row( $plugin ) {
		// Bailout.
		if ( ! $plugin ) {
			return '';
		}

		$is_license         = $plugin['License'] && ! empty( $plugin['License']['license_key'] );
		$expires_timestamp  = $is_license ? strtotime( $plugin['License']['expires'] ) : '';
		$is_license_expired = $is_license && ( 'expired' === $plugin['License']['license'] || $expires_timestamp < current_time( 'timestamp', 1 ) );

		ob_start();
		?>
		<div class="give-row give-border give-plugin__info">
			<div class="give-left">
				<span class="give-text give-plugin__name"><?php echo $plugin['Name']; ?></span>
				<span class="give-text">
						<?php
						echo sprintf(
							'<a href="%1$s" class="give-ajax-modal" title="%3$s">%2$s</a>',
							give_modal_ajax_url( array(
								'url'            => urlencode_deep( give_get_addon_readme_url( $plugin['ChangeLogSlug'] ) ),
								'show_changelog' => 1,
							) ),
							__( 'changelog', 'give' ),
							__( 'Changelog of' ) . " {$plugin['Name']}"
						);
						?>
					</span>
			</div>
			<div class="give-right">
				<span class="give-text"><?php echo sprintf( '%1$s %2$s', __( 'Version' ), $plugin['Version'] ) ?></span>
				<?php
				if ( in_array( $plugin['Status'], array( 'active', 'inactive' ) ) ) {
					echo sprintf(
						'<span class="give-background__gray give-border give-text give-text_small give-plugin__status">%1$s %2$s</span>',
						__( 'currently', 'give' ),
						'active' === $plugin['Status'] ? __( 'activated', 'give' ) : __( 'installed', 'give' )
					);
				}


				printf(
					'<span><%3$s class="give-button button-secondary" target="_blank" href="%1$s"%4$s><i class="dashicons dashicons-download"></i>%2$s</%3$s></span>',
					$plugin['DownloadURL'],
					__( 'Download', 'give' ),
					$is_license_expired || ! $plugin['DownloadURL'] ? 'button' : 'a',
					$is_license_expired || ! $plugin['DownloadURL'] ? ' disabled' : ''
				);
				?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}


	/**
	 * Get plugin information by id.
	 * Note: only for internal use
	 *
	 * @param $plugin_id
	 *
	 * @return array
	 * @since 2.5.0
	 *
	 */
	public static function get_plugin_by_item_name( $plugin_id ) {
		$give_plugins = give_get_plugins();
		$plugin       = array();

		foreach ( $give_plugins as $give_plugin ) {
			$addon_shortname = Give_License::get_short_name( $give_plugin['Name'] );
			$addon_slug      = str_replace( '_', '-', $addon_shortname );
			$addon_id        = str_replace( 'give-', '', $addon_slug );

			if ( $addon_id === $plugin_id ) {
				$plugin = $give_plugin;
				break;
			}
		}

		return $plugin;
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
	add_thickbox();
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
						disabled
					>
				</form>
			</div>

			<p class="give-field-description"><?php _e( 'Enter a license key above to unlock your GiveWP add-ons. You can access your licenses anytime from the My Account section on the GiveWP website.' ); ?></p>
		</div>

		<h2><?php _e( 'License and Downloads', 'give' ); ?></h2>
		<button
			id="give-button__refresh-licenses"
			class="button-secondary"
			data-activate="<?php _e( 'Refresh all licenses', 'give' ); ?>"
			data-activating="<?php _e( 'Refreshing all licenses...', 'give' ); ?>"
			data-nonce="<?php echo wp_create_nonce( 'give-refresh-all-licenses' ); ?>"
		>
			<?php _e( 'Refresh All Licenses', 'give' ); ?>
		</button>
		<section id="give-licenses-container">
			<?php echo Give_Addons::render_license_section(); ?>
		</section>
		<?php //give_add_ons_feed(); @todo: enabled this function when create pr ?>
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

// @todo: convert all staging site link to live site
// @todo check if all plugin follow download file and github repo naming standards
