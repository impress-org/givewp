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
use Give\Log\Log;

if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists('Give_License') ) :

    /**
     * Give_License Class
     *
     * This class simplifies the process of adding license information
     * to new Give add-ons.
     *
     * @since 1.0
     */
    class Give_License
    {

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
        private static $site_url = 'https://givewp.com/';

        /**
         * API URL
         *
         * @access private
         * @since  1.0
         *
         * @var    string
         */
        private static $api_url = 'https://givewp.com/edd-sl-api/';

        /**
         * array of licensed addons
         *
         * @since  2.1.4
         * @access private
         *
         * @var    array
         */
        private static $licensed_addons = [];

        /**
         * Account URL
         *
         * @access private
         * @since  1.7
         *
         * @var null|string
         */
        private static $account_url = 'http://docs.givewp.com/settings-account';

        /**
         * Downloads URL
         *
         * @access private
         * @since  2.5.0
         *
         * @var null|string
         */
        private static $downloads_url = 'http://docs.givewp.com/settings-downloads';

        /**
         * Checkout URL
         *
         * @access private
         * @since  1.7
         *
         * @var null|string
         */
        private static $checkout_url = 'https://givewp.com/checkout/';

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
			self::$api_url          = is_null( $_api_url ) ? self::$api_url : $_api_url;
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
         * @param bool $response_in_array
         *
         * @return mixed
         * @deprecated 2.5.0 Use self::request_license_api instead.
         *
         * @since      1.8.9
         * @access     public
         */
        public function get_license_info($edd_action = '', $response_in_array = false)
        {
            if ( empty($edd_action) ) {
                return false;
            }

            give_doing_it_wrong(__FUNCTION__, 'Use self::request_license_api instead from GiveWP 2.5.0');

            // Data to send to the API.
            $api_params = [
                'edd_action' => $edd_action, // never change from "edd_" to "give_"!
                'license'    => $this->license,
                'item_name'  => urlencode($this->item_name),
            ];

            return self::request_license_api($api_params, $response_in_array);
        }

        /**
         * Return licensed addons info
         *
         * Note: note only for internal logic
         *
         * @since 2.1.4
         * @return array
         */
        static function get_licensed_addons()
        {
            return self::$licensed_addons;
        }


        /**
         * Check if license key attached to subscription
         *
         * @since 2.5.0
         *
         * @param string $license_key
         *
         * @return array
         */
        static function is_subscription($license_key = '')
        {
            // Check if current license is part of subscription or not.
            $subscriptions = get_option('give_subscriptions');
            $subscription  = [];

            if ( $subscriptions ) {
                foreach ($subscriptions as $subs) {
                    if ( in_array($license_key, $subs['licenses']) ) {
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
         * @since  1.8.9
         * @since 2.18.0 log failed license api request information
         *
         * @param bool $response_in_array
         *
         * @param array $api_params
         *
         * @return array|WP_Error
         */
        public static function request_license_api($api_params = [], $response_in_array = false)
        {
            // Bailout.
            if ( empty($api_params['edd_action']) ) {
                return new WP_Error('give-invalid-edd-action', __('Valid edd_action not defined', 'give'));
            }

            // Data to send to the API.
            $default_api_params = [
                // 'edd_action' => $edd_action, never change from "edd_" to "give_"!
                // 'license'    => $this->license,
                // 'item_name'  => urlencode( $this->item_name ),
                'url' => home_url(),
            ];

            $api_params = wp_parse_args($api_params, $default_api_params);

            // Call the API.
            $response = wp_remote_post(
                self::$api_url,
                apply_filters(
                    'give_request_license_api_args',
                    [
                        'timeout'   => 15,
                        'sslverify' => false,
                        'body'      => $api_params,
                    ]
                )
            );

            $statusCode = wp_remote_retrieve_response_code($response);
            $body       = json_decode(wp_remote_retrieve_body($response), $response_in_array);

            if ( 200 !== $statusCode ) {
                Log::http(
                    'License Api request failed',
                    [
                        'category'    => 'License',
                        'api url'     => self::$api_url,
                        'request'     => $api_params,
                        'status code' => $statusCode,
                        'response'    => $response
                    ]
                );
            }

            // Make sure there are no errors.
            if ( is_wp_error($response) ) {
                return $response;
            }

            return $body;
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
			$license       = [];
			$give_licenses = get_option( 'give_licenses', [] );

			if ( ! empty( $give_licenses ) ) {
				foreach ( $give_licenses as $give_license ) {

					// Logic to match all access pass license to add-on.
					$compares = $give_license['is_all_access_pass']
						? $give_license['download']

						// Prevent PHP notice if somehow automatic update does not run properly.
						// Because plugin_slug will only define in updated license rest api response.
						: [ [ 'plugin_slug' => ! empty( $give_license['plugin_slug'] ) ? $give_license['plugin_slug'] : '' ] ];

					foreach ( $compares as $compare ) {
						if ( ! empty( $compare['plugin_slug'] ) && $plugin_dirname === $compare['plugin_slug'] ) {
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

			return $is_match_found ? $give_plugins[ $is_match_found ] : [];
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
			$plugin_name = str_replace( [ '-', 'give ' ], [ ' ', 'Give - ' ], $plugin_slug );

			return ucwords( $plugin_name );
		}

		/**
		 * Render license section
		 *
		 * @return string
		 * @since 2.5.0
		 */
		public static function render_licenses_list() {
			$give_plugins           = give_get_plugins( [ 'only_premium_add_ons' => true ] );
			$give_licenses          = get_option( 'give_licenses', [] );
			$licenses_without_addon = $give_licenses;

			// Get all access pass licenses
			$all_access_pass_licenses   = [];
			$all_access_pass_addon_list = [];
			foreach ( $give_licenses as $key => $give_license ) {
				if ( $give_license['is_all_access_pass'] ) {
					$all_access_pass_licenses[ $key ] = $give_license;

					unset( $licenses_without_addon[ $key ] );

					foreach ( $give_license['download'] as $download ) {
						$all_access_pass_addon_list[] = $download['plugin_slug'];
					}
				}
			}

			$html = [
				'unlicensed'             => '',
				'licensed'               => '',
				'licenses_without_addon' => '',
				'all_access_licensed'    => '',
			];

			if ( ! empty( $give_plugins ) ) {
				foreach ( $give_plugins as $give_plugin ) {
					if ( in_array( $give_plugin['Dir'], $all_access_pass_addon_list ) ) {
						continue;
					}

					$addon_license = self::get_license_by_plugin_dirname( $give_plugin['Dir'] );
					$html_arr_key  = 'unlicensed';

					if ( $addon_license ) {
						$html_arr_key = 'licensed';
						unset( $licenses_without_addon[ $addon_license['license_key'] ] );
					}

					$html[ "{$html_arr_key}" ] .= self::html_by_plugin( $give_plugin );
				}
			}

			if ( ! empty( $all_access_pass_licenses ) ) {
				foreach ( $all_access_pass_licenses as $key => $all_access_pass_license ) {
					$html['all_access_licensed'] .= self::html_by_license( $all_access_pass_license );
				}
			}

			if ( ! empty( $licenses_without_addon ) ) {
				foreach ( $licenses_without_addon as $key => $license ) {
					if ( in_array( $license['plugin_slug'], $all_access_pass_addon_list ) ) {
						continue;
					}
					$html['licenses_without_addon'] .= self::html_by_license( $license );
				}
			}

			ksort( $html );

			// After sorting order will be all_access_licensed -> licensed ->  unlicensed

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

			$default_plugin = [
				'ChangeLogSlug' => $plugin['Dir'],
				'DownloadURL'   => '',
			];

			if ( false !== strpos( $default_plugin['ChangeLogSlug'], '-gateway' ) ) {
				// We found that each gateway addon does not have `-gateway` in changelog file slug
				$default_plugin['ChangeLogSlug'] = str_replace( '-gateway', '', $default_plugin['ChangeLogSlug'] );
			}

			if ( $license ) {
				$license['renew_url']            = self::$checkout_url . "?edd_license_key={$license['license_key']}";
				$default_plugin['ChangeLogSlug'] = $license['readme'];
				$default_plugin['DownloadURL']   = $license['download'];
			}

			$plugin['License'] = $license = wp_parse_args(
				$license,
				[
					'item_name'     => str_replace( 'give-', '', $plugin['Dir'] ),
					'purchase_link' => $plugin['PluginURI'],
				]
			);

			$plugin = wp_parse_args( $plugin, $default_plugin );
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

					$addons = $license['download'];

					if ( empty( $license['is_all_access_pass'] ) ) {
						$addons = [ $license ];
					}

					foreach ( $addons as $addon ) {
						$default_plugin = [
							// In single license key we will get item_name instead of name.
							'Name'          => ! empty( $addon['item_name'] ) ? $addon['item_name'] : $addon['name'],

							'ChangeLogSlug' => $addon['readme'],
							'Version'       => $addon['current_version'],
							'Status'        => 'not installed',

							// In single license key we will get download instead of file.
							'DownloadURL'   => ! empty( $addon['download'] ) ? $addon['download'] : $addon['file'],

						];

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
		private static function html_license_row( $license, $plugin = [] ) {
			ob_start();

			$is_license          = $license && ! empty( $license['license_key'] );
			$license_key         = $is_license ? $license['license_key'] : '';
			$license_is_inactive = $license_key && ! in_array( $license['license'], [ 'valid', 'expired' ] );
			$expires_timestamp   = $is_license ? strtotime( $license['expires'] ) : '';
			$is_license_expired  = $is_license && ( 'expired' === $license['license'] || $expires_timestamp < time() );
			$addon_dir           = ! empty( $plugin['Dir'] ) ? $plugin['Dir'] : ( ! empty( $license['plugin_slug'] ) ? $license['plugin_slug'] : '' );
			?>
			<div class="give-license-row give-clearfix">
				<div class="give-license-notice-container"></div>
				<div class="give-license-top give-clearfix">

					<div class="give-license-top-column give-license-key-field-wrap">

						<div class="give-license__key<?php echo $license_key ? ' give-has-license-key' : ''; ?>">
							<?php $value = $license_key ? give_hide_char( $license['license_key'], 5 ) : ''; ?>
							<label for="give-license-addon-key-field" class="give-license-top-header"><?php _e( 'License Key', 'give' ); ?></label>
							<input id="give-license-addon-key-field" type="text" autocomplete="off" value="<?php echo $value; ?>"<?php echo $value ? ' readonly' : ''; ?>>
							<?php if ( ! $license_key ) : ?>
								<button class="give-button__license-activate button-primary" data-addon="<?php echo $addon_dir; ?>">
									<?php _e( 'Activate', 'give' ); ?>
								</button>
							<?php elseif ( $license_is_inactive ) : ?>
								<button class="give-button__license-reactivate button-primary" data-addon="<?php echo $addon_dir; ?>" data-license="<?php echo $license['license_key']; ?>">
									<?php _e( 'Reactivate', 'give' ); ?>
								</button>
							<?php else : ?>

								<?php
								echo sprintf(
									'<button class="give-license__deactivate button button-secondary" data-license-key="%2$s" data-item-name="%3$s"  data-plugin-dirname="%5$s" data-nonce="%4$s">%1$s</button>',
									__( 'Deactivate', 'give' ),
									$license['license_key'],
									$license['item_name'],
									wp_create_nonce( "give-deactivate-license-{$license['item_name']}" ),
									! empty( $license['plugin_slug'] ) ? $license['plugin_slug'] : ''
								);
								?>

							<?php endif; ?>

							<div class="give-license__status">
								<?php
								echo $license_key && ! $license_is_inactive
									? sprintf(
										'<span class="dashicons dashicons-%2$s"></span>&nbsp;%1$s',
										$is_license_expired
											? __( 'License is expired. Please activate your license key.', 'give' )
											: __( 'License is active and you are receiving updates and support.', 'give' ),
										$is_license_expired
											? 'no'
											: 'yes'
									)
									: sprintf(
										'<span class="dashicons dashicons-no"></span> %1$s %2$s',
										__( 'License is inactive.', 'give' ),
										$license_is_inactive
											? sprintf(
												__( 'Please <a href="%1$s" target="_blank">Visit your dashboard</a> to check this license details and activate this license to receive updates and support.', 'give' ),
												self::get_account_url()
											)
											: __( 'Please activate your license key.', 'give' )
									);
								?>
							</div>
						</div>
					</div>

					<div class="give-license-top-column give-license-info-field-wrap">
						<h3 class="give-license-top-header"><?php _e( 'License Information', 'give' ); ?></h3>
						<?php
						// @todo: handle all license status;
						if ( $license_key ) :
							?>

							<?php if ( $license_key ) : ?>
								<?php
								echo sprintf(
									'<p class="give-license-renewal-date"><span class="dashicons dashicons-calendar-alt"></span> <strong>%1$s</strong> %2$s</p>',
									$is_license_expired ? __( 'Expired:', 'give' ) : __( 'Renews:', 'give' ),
									date( give_date_format(), $expires_timestamp )
								);
								?>
							<?php endif; ?>

							<?php
							if ( $is_license_expired ) {
								// @todo: need to test renew license link
								echo sprintf(
									'<a href="%1$s" target="_blank">%2$s</a>',
									$license['renew_url'],
									__( 'Renew to manage sites', 'give' )
								);
							} elseif ( $license_key ) {
								if ( ! $license['activations_left'] ) {
									echo sprintf(
										'<span class="give-license-activations-left">%1$s</span>',
										__( 'No activations remaining', 'give' )
									);
								} else {
									echo sprintf(
										'<span class="give-license-activations-left"><span class="give-license-activations-remaining-icon wp-ui-highlight">%1$s</span> %2$s</span>',
										$license['activations_left'],
										_n( 'Activation Remaining', 'Activations Remaining', $license['activations_left'], 'give' )
									);
								}
							}
							?>
						<?php else : ?>

							<p class="give-field-description"><?php _e( 'This is an unlicensed add-on and is not receiving updates or support. Please activate your license key to fix the issue.', 'give' ); ?></p>

						<?php endif; ?>
					</div>

					<div class="give-license-top-column">
						<h3 class="give-license-top-header"><?php _e( 'License Actions', 'give' ); ?></h3>

						<?php
						// Purchase license link.
						if ( ! $license_key ) :
							?>
							<?php
							echo sprintf(
								'<a class="give-button button-secondary" href="%1$s" target="_blank">%2$s</a>',
								$license['purchase_link'],
								__( 'Purchase License', 'give' )
							);
							?>
						<?php endif; ?>

						<?php
						// Manage license link on GiveWP.com
						if ( ! $is_license_expired && $license_key ) {
							echo sprintf(
								'<a href="%1$spurchase-history/?license_id=%2$s&action=manage_licenses&payment_id=%3$s" target="_blank" class="give-license-action-link">%4$s</a>',
								trailingslashit( self::get_website_url() ),
								$license['license_id'],
								$license['payment_id'],
								__( 'Manage License', 'give' )
							);
							echo sprintf(
								'<a href="%1$spriority-support/" target="_blank" class="give-license-action-link">%2$s</a>',
								trailingslashit( self::get_website_url() ),
								__( 'Access Support', 'give' )
							);
						}
						?>

					</div>
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
			$is_license_expired = $is_license && ( 'expired' === $plugin['License']['license'] || $expires_timestamp < time() );
			ob_start();
			?>
			<div class="give-addon-info-wrap give-clearfix">

				<div class="give-addon-info-left">
					<span class="give-addon-name">
						<?php echo $plugin['Name']; ?>
					</span>
					<span class="give-addon-version">
						<?php echo sprintf( '%1$s %2$s', __( 'Version', 'give' ), $plugin['Version'] ); ?>
					</span>
				</div>

				<div class="give-addon-info-right">
					<?php
					echo sprintf(
						'<a href="%1$s" class="give-ajax-modal give-addon-view-changelog" title="%3$s">%2$s</a>',
						give_modal_ajax_url(
							[
								'url'            => filter_var( $plugin['ChangeLogSlug'], FILTER_VALIDATE_URL )
									? urldecode_deep( $plugin['ChangeLogSlug'] )
									: urlencode_deep( give_get_addon_readme_url( $plugin['ChangeLogSlug'] ) ),
								'show_changelog' => 1,
							]
						),
						__( 'View Changelog', 'give' ),
						__( 'Changelog of', 'give' ) . " {$plugin['Name']}"
					);
					?>

					<?php
					// Activation status.
					if ( in_array( $plugin['Status'], [ 'active', 'inactive' ] ) ) {
						echo sprintf(
							'<span class="give-addon-activation-status give-addon-activation-status__%1$s">%1$s</span>',
							'active' === $plugin['Status'] ? __( 'activated', 'give' ) : __( 'installed', 'give' )
						);
					}

					printf(
						'<%3$s class="give-button button button-secondary button-small" href="%1$s"%4$s><span class="dashicons dashicons-download"></span>%2$s</%3$s>',
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
		 * @return mixed|void
		 * @since 2.5.0
		 */
		public static function refresh_license_status() {
			return get_option(
				'give_licenses_refreshed_last_checked',
				[
					'compare' => date( 'Ymd' ),
					'time'    => time(),
					'count'   => 0,
				]
			);
		}

		/**
		 * Get all download slugs from all access pass key.
		 * Note: only for internal use and will be refactored in the  future.
		 *
		 * @param array $license All access pass license data
		 *
		 * @return string[]
		 * @since 2.6.3
		 */
		public static function getAddonSlugsFromAllAccessPassLicense( $license ) {
			$result = [];

			foreach ( $license['download'] as $download ) {
				$result[] = $download['plugin_slug'];
			}

			return $result;
		}
	}

endif; // end class_exists check.
