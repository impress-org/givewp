<?php
/**
 * Give License handler
 *
 * @package     Give
 * @subpackage  Admin/License
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_License' ) ) :

	/**
	 * Give_License Class
	 *
	 * This class simplifies the process of adding license information
	 * to new Give add-ons.
	 *
	 * @since 1.0
	 */
	class Give_License {

		/**
		 * File
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @var    string
		 */
		private $file;

		/**
		 * License
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @var    string
		 */
		private $license;

		/**
		 * Item name
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @var    string
		 */
		private $item_name;

		/**
		 * Item ID
		 *
		 * @access private
		 * @since  2.2.4
		 *
		 * @var    int
		 */
		private $item_id;

		/**
		 * License Information object.
		 *
		 * @access private
		 * @since  1.7
		 *
		 * @var    object
		 */
		private $license_data;

		/**
		 * Item shortname
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @var    string
		 */
		private $item_shortname;

		/**
		 * Version
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @var    string
		 */
		private $version;

		/**
		 * Author
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @var    string
		 */
		private $author;

		/**
		 * Plugin directory name
		 *
		 * @access private
		 * @since  2.5.0
		 *
		 * @var    string
		 */
		private $plugin_dirname;

		/**
		 * Website URL
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @var    string
		 */
		private static $site_url = 'http://staging.givewp.com/';

		/**
		 * API URL
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @var    string
		 */
		private $api_url = 'https://staging.givewp.com/edd-sl-api/';

		/**
		 * array of licensed addons
		 *
		 * @since  2.1.4
		 * @access private
		 *
		 * @var    array
		 */
		private static $licensed_addons = array();

		/**
		 * Account URL
		 *
		 * @access private
		 * @since  1.7
		 *
		 * @var null|string
		 */
		private static $account_url = 'http://staging.givewp.com/my-account/';

		/**
		 * Downloads URL
		 *
		 * @access private
		 * @since  2.5.0
		 *
		 * @var null|string
		 */
		private static $downloads_url = 'http://staging.givewp.com/my-downloads/';

		/**
		 * Checkout URL
		 *
		 * @access private
		 * @since  1.7
		 *
		 * @var null|string
		 */
		private static $checkout_url = 'http://staging.givewp.com/checkout/';

		/**
		 * Class Constructor
		 *
		 * Set up the Give License Class.
		 *
		 * @access public
		 *
		 * @param string $_file
		 * @param string $_item_name
		 * @param string $_version
		 * @param string $_author
		 * @param string $_optname
		 * @param string $_api_url
		 * @param string $_checkout_url
		 * @param string $_account_url
		 * @param int    $_item_id
		 *
		 * @since  1.0
		 */
		public function __construct(
			$_file,
			$_item_name,
			$_version,
			$_author,
			$_optname = null,
			$_api_url = null,
			$_checkout_url = null,
			$_account_url = null,
			$_item_id = null
		) {

			// Only load in wp-admin.
			if ( ! is_admin() ) {
				return;
			}

			if ( is_numeric( $_item_id ) ) {
				$this->item_id = absint( $_item_id );
			}

			$this->file             = $_file;
			$this->item_name        = $_item_name;
			$this->plugin_dirname   = dirname( plugin_basename( $this->file ) );
			$this->item_shortname   = self::get_short_name( $this->item_name );
			$this->license_data     = self::get_license_by_plugin_dirname( $this->plugin_dirname );
			$this->version          = $_version;
			$this->license          = ! empty( $this->license_data['license_key'] ) ? $this->license_data['license_key'] : '';
			$this->author           = $_author;
			$this->api_url          = is_null( $_api_url ) ? $this->api_url : $_api_url;
			self::$checkout_url     = is_null( $_checkout_url ) ? self::$checkout_url : $_checkout_url;
			self::$account_url      = is_null( $_account_url ) ? self::$account_url : $_account_url;
			$this->auto_updater_obj = null;

			// Add plugin to registered licenses list.
			array_push( self::$licensed_addons, plugin_basename( $this->file ) );
		}


		/**
		 * Get plugin shortname
		 *
		 * @param $plugin_name
		 *
		 * @return string
		 * @since  2.1.0
		 * @access public
		 */
		public static function get_short_name( $plugin_name ) {
			$plugin_name = trim( str_replace( 'Give - ', '', $plugin_name ) );
			$plugin_name = 'give_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $plugin_name ) ) );

			return $plugin_name;
		}

		/**
		 * Activate License
		 *
		 * Activate the license key.
		 *
		 * @access public
		 * @return void
		 * @since  1.0
		 */
		public function activate_license() {
		}

		/**
		 * Deactivate License
		 *
		 * Deactivate the license key.
		 *
		 * @access public
		 * @return void
		 * @since  1.0
		 */
		public function deactivate_license() {
		}


		/**
		 * Get license information.
		 *
		 * @param string $edd_action
		 * @param bool   $response_in_array
		 *
		 * @return mixed
		 * @deprecated 2.5.0 Use self::request_license_api instead.
		 *
		 * @since      1.8.9
		 * @access     public
		 */
		public function get_license_info( $edd_action = '', $response_in_array = false ) {

			if ( empty( $edd_action ) ) {
				return false;
			}

			give_doing_it_wrong( __FUNCTION__, 'Use self::request_license_api instead', '2.5.0' );

			// Data to send to the API.
			$api_params = array(
				'edd_action' => $edd_action, // never change from "edd_" to "give_"!
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
			);

			return self::request_license_api( $api_params, $response_in_array );
		}

		/**
		 * Return licensed addons info
		 *
		 * Note: note only for internal logic
		 *
		 * @return array
		 * @since 2.1.4
		 */
		static function get_licensed_addons() {
			return self::$licensed_addons;
		}


		/**
		 * Check if license key attached to subscription
		 *
		 * @param string $license_key
		 *
		 * @return array
		 * @since 2.5.0
		 */
		static function is_subscription( $license_key = '' ) {
			// Check if current license is part of subscription or not.
			$subscriptions = get_option( 'give_subscriptions' );
			$subscription  = array();

			if ( $subscriptions ) {
				foreach ( $subscriptions as $subs ) {
					if ( in_array( $license_key, $subs['licenses'] ) ) {
						$subscription = $subs;
						break;
					}
				}
			}

			return $subscription;
		}

		/**
		 * Get license information.
		 *
		 * @param array $api_params
		 * @param bool  $response_in_array
		 *
		 * @return array|WP_Error
		 * @since  1.8.9
		 * @access public
		 */
		public static function request_license_api( $api_params = array(), $response_in_array = false ) {
			// Bailout.
			if ( empty( $api_params['edd_action'] ) ) {
				return new WP_Error( 'give-invalid-edd-action', __( 'Valid edd_action not defined', 'give' ) );
			}

			// Data to send to the API.
			$default_api_params = array(
				// 'edd_action' => $edd_action, never change from "edd_" to "give_"!
				// 'license'    => $this->license,
				// 'item_name'  => urlencode( $this->item_name ),
				'url' => home_url(),
			);

			$api_params = wp_parse_args( $api_params, $default_api_params );

			// Call the API.
			$response = wp_remote_post(
				self::$checkout_url,
				apply_filters(
					'give_request_license_api_args',
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'body'      => $api_params,
					)
				)
			);

			// Make sure there are no errors.
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			return json_decode( wp_remote_retrieve_body( $response ), $response_in_array );
		}

		/**
		 * Get license by plugin dirname
		 *
		 * @param string $plugin_dirname
		 *
		 * @return array
		 *
		 * @since  2.5.0
		 * @access public
		 */
		public static function get_license_by_plugin_dirname( $plugin_dirname ) {
			$license       = array();
			$give_licenses = get_option( 'give_licenses', array() );

			if ( ! empty( $give_licenses ) ) {
				foreach ( $give_licenses as $give_license ) {

					// Logic to match all access pass license to add-on.
					$compares = is_array( $give_license['download'] )
						? $give_license['download']
						: array( array( 'plugin_slug' => $give_license['plugin_slug'] ) );

					foreach ( $compares as $compare ) {
						if ( $plugin_dirname === $compare['plugin_slug'] ) {
							$license = $give_license;
							break;
						}
					}
				}
			}

			return $license;
		}


		/**
		 * Get checkout url
		 *
		 * @return string|null
		 * @since 2.5.0
		 */
		public static function get_checkout_url() {
			return self::$checkout_url;
		}

		/**
		 * Get account url
		 *
		 * @return string|null
		 * @since 2.5.0
		 */
		public static function get_account_url() {
			return self::$account_url;
		}

		/**
		 * Get downloads url
		 *
		 * @return string|null
		 * @since 2.5.0
		 */
		public static function get_downloads_url() {
			return self::$downloads_url;
		}

		/**
		 * Get account url
		 *
		 * @return string|null
		 * @since 2.5.0
		 */
		public static function get_website_url() {
			return self::$site_url;
		}


		/**
		 * Get plugin information by id.
		 * Note: only for internal use
		 *
		 * @param string $plugin_slug
		 *
		 * @return array
		 * @since 2.5.0
		 */
		public static function get_plugin_by_slug( $plugin_slug ) {
			$give_plugins   = give_get_plugins();
			$matching_list  = wp_list_pluck( $give_plugins, 'Dir', 'Path' );
			$is_match_found = array_search( $plugin_slug, $matching_list, true );

			return $is_match_found ? $give_plugins[ $is_match_found ] : array();
		}

		/**
		 * Get plugin information by id.
		 * Note: only for internal use
		 *
		 * @param string $plugin_slug
		 *
		 * @return string
		 * @since 2.5.0
		 */
		public static function build_plugin_name_from_slug( $plugin_slug ) {
			$plugin_name = str_replace( array( '-', 'give ' ), array( ' ', 'Give - ' ), $plugin_slug );

			return ucwords( $plugin_name );
		}

		/**
		 * Render license section
		 *
		 * @return string
		 * @since 2.5.0
		 */
		public static function render_licenses_list() {
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

				if ( in_array( $give_plugin['Dir'], $all_access_pass_addon_list ) ) {
					continue;
				}

				$addon_license = self::get_license_by_plugin_dirname( $give_plugin['Dir'] );
				$html_arr_key  = 'unlicensed';

				if ( $addon_license ) {
					$html_arr_key = 'licensed';
				}

				$html[ "{$html_arr_key}" ] .= self::html_by_plugin( $give_plugin );
			}

			if ( ! empty( $all_access_pass_licenses ) ) {
				foreach ( $all_access_pass_licenses as $key => $all_access_pass_license ) {
					$html['all_access_licensed'] .= self::html_by_license( $all_access_pass_license );
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
		 */
		public static function html_by_plugin( $plugin ) {
			// Bailout.
			if ( empty( $plugin ) ) {
				return '';
			}

			ob_start();
			$license = self::get_license_by_plugin_dirname( $plugin['Dir'] );

			$default_plugin = array(
				'ChangeLogSlug' => $plugin['Dir'],
				'DownloadURL'   => '',
			);

			if ( false !== strpos( $default_plugin['ChangeLogSlug'], '-gateway' ) ) {
				// We found that each gateway addon does not have `-gateway` in changelog file slug
				$default_plugin['ChangeLogSlug'] = str_replace( '-gateway', '', $default_plugin['ChangeLogSlug'] );
			}

			if ( $license ) {
				$license['renew_url']            = self::$checkout_url . "?edd_license_key={$license['license_key']}";
				$default_plugin['ChangeLogSlug'] = $license['readme'];

				// Backward compatibility.
				if ( ! empty( $license['subscription'] ) ) {
					$license['expires']            = $license['subscription']['expires'];
					$default_plugin['DownloadURL'] = $license['download'];

					$license['renew_url'] = self::$checkout_url . "?edd_license_key={$license['subscription']['subscription_key']}";
				}
			}

			$plugin['License'] = $license = wp_parse_args(
				$license, array(
					'item_name'     => str_replace( 'give-', '', $plugin['Dir'] ),
					'purchase_link' => $plugin['PluginURI'],
				)
			);

			$plugin = wp_parse_args( $plugin, $default_plugin )
			?>
			<div class="give-addon-wrap">
				<div class="give-addon-inner">
					<?php echo self::html_license_row( $license, $plugin ); ?>
					<?php echo self::html_plugin_row( $plugin ); ?>
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
		 */
		private static function html_by_license( $license ) {
			ob_start();

			$license['renew_url'] = self::$checkout_url . "?edd_license_key={$license['license_key']}";
			?>
			<div class="give-addon-wrap">
				<div class="give-addon-inner">
					<?php
					echo self::html_license_row( $license );

					foreach ( $license['download'] as $addon ) {
						$default_plugin = array(
							'Name'          => $addon['name'],
							'ChangeLogSlug' => $addon['readme'],
							'Version'       => $addon['current_version'],
							'Status'        => 'not installed',
							'DownloadURL'   => $addon['file'],
						);

						$plugin = wp_parse_args(
							self::get_plugin_by_slug( $addon['plugin_slug'] ),
							$default_plugin
						);

						$plugin['Name'] = false !== strpos( $plugin['Name'], 'Give' )
							? $plugin['Name']
							: self::build_plugin_name_from_slug( $addon['plugin_slug'] );

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
		private static function html_license_row( $license, $plugin = array() ) {
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
							&nbsp;&nbsp
							<button class="give-button__license-activate button-secondary" disabled data-addon="<?php echo $plugin['Dir']; ?>"><?php _e( 'Activate License' ); ?></button>
						<?php endif; ?>
					</span>

					<?php
					// @todo: handle all license status;
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
						} elseif ( ! $license['activations_left'] ) {
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

						echo sprintf(
							'<span class="give-text"><a href="%9$spurchase-history/?license_id=%3$s&action=manage_licenses&payment_id=%4$s" target="_blank">%1$s</a> | <a href="javascript:void(0)" target="_blank" class="give-license__deactivate" data-license-key="%5$s" data-item-name= "%6$s" data-nonce="%7$s" data-plugin-dirname="%8$s">%2$s</a> </span>',
							__( 'Visit site', 'give' ),
							__( 'Deactivate', 'give' ),
							$license['license_id'],
							$license['payment_id'],
							$license['license_key'],
							$license['item_name'],
							wp_create_nonce( "give-deactivate-license-{$license['item_name']}" ),
							! empty( $license['plugin_slug'] ) ? $license['plugin_slug'] : '',
							Give_License::get_website_url()
						);
					}
					?>
				</div>
				<div class="give-right">
					<?php if ( ! $license_key ) : ?>
						<span class="give-text"><?php _e( 'Not receiving updates or support' ); ?></span>
						<span>
						<?php
						// help: https://docs.easydigitaldownloads.com/article/268-creating-custom-add-to-cart-links
						echo sprintf(
							'<a class="give-button button-secondary" href="%1$s" target="_blank">%2$s</a>',
							$license['purchase_link'],
							__( 'Purchase license', 'give' )
						);
						?>
					</span>
					<?php else : ?>
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
							give_modal_ajax_url(
								array(
									'url'            => filter_var( $plugin['ChangeLogSlug'], FILTER_VALIDATE_URL )
										? urldecode_deep( $plugin['ChangeLogSlug'] )
										: urlencode_deep( give_get_addon_readme_url( $plugin['ChangeLogSlug'] ) ),
									'show_changelog' => 1,
								)
							),
							__( 'changelog', 'give' ),
							__( 'Changelog of' ) . " {$plugin['Name']}"
						);
						?>
					</span>
				</div>
				<div class="give-right">
					<?php
					if ( in_array( $plugin['Status'], array( 'active', 'inactive' ) ) ) {
						echo sprintf(
							'<span class="give-background__gray give-border give-text give-text_small give-plugin__status">%1$s %2$s</span>',
							__( 'currently', 'give' ),
							'active' === $plugin['Status'] ? __( 'activated', 'give' ) : __( 'installed', 'give' )
						);
					}

					printf(
						'<span class="give-text">%1$s %2$s</span>',
						__( 'Version', 'give' ),
						$plugin['Version']
					);

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
		 * Get refresh license status
		 *
		 * @since 2.5.0
		 * @return mixed|void
		 */
		public static function refresh_license_status() {
			return get_option(
				'give_licenses_refreshed_last_checked',
				array(
					'compare' => date( 'Ymd' ),
					'time'    => current_time( 'timestamp', 1 ),
					'count'   => 0,
				)
			);
		}
	}

endif; // end class_exists check.
