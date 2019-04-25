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
		 * API URL
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @var    string
		 */
		private $api_url = 'https://givewp.com/edd-sl-api/';

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
		private static $account_url = 'https://givewp.com/my-account/';

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
		 *
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
			$this->item_shortname   = self::get_short_name( $this->item_name );
			$this->license_data     = self::get_license_by_plugin_dirname( basename( dirname( $_file ) ) );
			$this->version          = $_version;
			$this->license          = ! empty( $this->license_data['license_key'] ) ? $this->license_data['license_key'] : '';
			$this->author           = $_author;
			$this->api_url          = is_null( $_api_url ) ? $this->api_url : $_api_url;
			self::$checkout_url     = is_null( $_checkout_url ) ? self::$checkout_url : $_checkout_url;
			self::$account_url      = is_null( $_account_url ) ? self::$account_url : $_account_url;
			$this->auto_updater_obj = null;

			// Add Setting for Give Add-on activation status.
			$is_addon_activated = Give_Cache_Setting::get_option( 'give_is_addon_activated' );
			if ( ! $is_addon_activated && is_object( $this ) ) {
				update_option( 'give_is_addon_activated', true, false );
				Give_Cache::set( 'give_cache_hide_license_notice_after_activation', true, DAY_IN_SECONDS );
			}

			// Add plugin to registered licenses list.
			array_push( self::$licensed_addons, plugin_basename( $this->file ) );

			// Setup hooks
			$this->includes();
			$this->hooks();

		}


		/**
		 * Get plugin shortname
		 *
		 * @param $plugin_name
		 *
		 * @return string
		 * @since  2.1.0
		 * @access public
		 *
		 */
		public static function get_short_name( $plugin_name ) {
			$plugin_name = trim( str_replace( 'Give - ', '', $plugin_name ) );
			$plugin_name = 'give_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $plugin_name ) ) );

			return $plugin_name;
		}

		/**
		 * Includes
		 *
		 * Include the updater class.
		 *
		 * @access private
		 * @return void
		 * @since  1.0
		 *
		 */
		private function includes() {

			if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
				require_once 'admin/EDD_SL_Plugin_Updater.php';
			}
		}

		/**
		 * Hooks
		 *
		 * Setup license hooks.
		 *
		 * @access private
		 * @return void
		 * @since  1.0
		 *
		 */
		private function hooks() {
			// Updater.
			add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );

			// Show addon notice on plugin page.
			$plugin_name = explode( 'plugins/', $this->file );
			$plugin_name = end( $plugin_name );
			add_action( "after_plugin_row_{$plugin_name}", array( $this, 'plugin_page_notices' ), 10, 3 );

		}


		/**
		 * Auto Updater
		 *
		 * @access private
		 * @return void
		 * @since  1.0
		 *
		 */
		public function auto_updater() {
			// @todo: test auto updater with all access pass

			if ( ! empty( $this->item_id ) ) {
				$args['item_id'] = $this->item_id;
			} else {
				$args['item_name'] = $this->item_name;
			}

			// Setup the updater.
			$this->auto_updater_obj = new EDD_SL_Plugin_Updater(
				$this->api_url,
				$this->file,
				array(
					'version'   => $this->version,
					'license'   => $this->license,
					'item_name' => $this->item_name,
					'author'    => $this->author,
				)
			);
		}

		/**
		 * Activate License
		 *
		 * Activate the license key.
		 *
		 * @access public
		 * @return void
		 * @since  1.0
		 *
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
		 *
		 */
		public function deactivate_license() {
		}


		/**
		 * Check if license is valid or not.
		 *
		 * @param null|object $licence_data
		 *
		 * @return bool
		 * @since  1.7
		 * @access public
		 *
		 */
		public function is_valid_license( $licence_data = null ) {
			$license_data = empty( $licence_data ) ? $this->license_data : $licence_data;

			if ( apply_filters( 'give_is_valid_license', ( $this->is_license( $license_data ) && 'valid' === $license_data->license ) ) ) {
				return true;
			}

			return false;
		}


		/**
		 * Check if license is license object or not.
		 *
		 * @param null|object $licence_data
		 *
		 * @return bool
		 * @since  1.7
		 * @access public
		 *
		 */
		public function is_license( $licence_data = null ) {
			$license_data = empty( $licence_data ) ? $this->license_data : $licence_data;

			if ( apply_filters( 'give_is_license', ( is_object( $license_data ) && ! empty( $license_data ) && property_exists( $license_data, 'license' ) ) ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Display plugin page licenses status notices.
		 *
		 * @param $plugin_file
		 * @param $plugin_data
		 * @param $status
		 *
		 * @return bool
		 */
		public function plugin_page_notices( $plugin_file, $plugin_data, $status ) {
			// Bailout.
			if ( $this->is_valid_license() ) {
				return false;
			}

			$update_notice_wrap = '<tr class="give-addon-notice-tr active"><td colspan="3" class="colspanchange"><div class="notice inline notice-warning notice-alt give-invalid-license"><p><span class="dashicons dashicons-info"></span> %s</p></div></td></tr>';
			$message            = $this->license_state_message();

			if ( ! empty( $message['message'] ) ) {
				echo sprintf( $update_notice_wrap, $message['message'] );
			}
		}


		/**
		 * Get message related to license state.
		 *
		 * @return array
		 * @since  1.8.7
		 * @access public
		 */
		public function license_state_message() {
			$message_data = array();

			if ( ! $this->is_valid_license() ) {

				$message_data['message'] = sprintf(
					'Please <a href="%1$s">activate your license</a> to receive updates and support for the %2$s add-on.',
					esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=licenses' ) ),
					$this->item_name
				);
			}

			return $message_data;
		}


		/**
		 * Get license information.
		 *
		 * @param string $edd_action
		 * @param bool   $response_in_array
		 *
		 * @return mixed
		 * @deprecated 2.5.0 Use Give_License::request_license_api instead.
		 *
		 * @since      1.8.9
		 * @access     public
		 */
		public function get_license_info( $edd_action = '', $response_in_array = false ) {

			if ( empty( $edd_action ) ) {
				return false;
			}

			give_doing_it_wrong( __FUNCTION__, 'Use Give_License::request_license_api instead', '2.5.0' );

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
		 *
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
		 *
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
		 * @return mixed
		 * @since  1.8.9
		 * @access public
		 *
		 */
		public static function request_license_api( $api_params = array(), $response_in_array = false ) {
			// Bailout.
			if ( empty( $api_params['edd_action'] ) ) {
				return false;
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
			// 'https://givewp.com/checkout/',
				'http://staging.givewp.com/chekout/', // For testing purpose
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
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
			$license        = array();
			$give_licenses  = get_option( 'give_licenses', array() );
			$plugin_dirname = strtolower( $plugin_dirname );


			if ( ! empty( $give_licenses ) ) {
				foreach ( $give_licenses as $give_license ) {

					// Logic to match all access pass license to add-on.
					$compares = is_array( $give_license['download'] )
						? $give_license['download']
						: array( array( 'plugin_slug' => $give_license['plugin_slug'] ) );

					foreach ( $compares as $compare ) {
						if ( $plugin_dirname === strtolower( $compare['plugin_slug'] ) ) {
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
		 * @return string|null
		 * @since 2.5.0
		 */
		public static function get_checkout_url() {
			return self::$checkout_url;
		}

		/**
		 * Get account url
		 * @return string|null
		 * @since 2.5.0
		 */
		public static function get_account_url() {
			return self::$account_url;
		}

		/**
		 * Get plugin information by id.
		 * Note: only for internal use
		 *
		 * @param string $plugin_slug
		 *
		 * @return array
		 * @since 2.5.0
		 *
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
		 *
		 */
		public static function build_plugin_name_from_slug( $plugin_slug ) {
			$plugin_name = str_replace( array( '-', 'give ' ), array( ' ', 'Give - ' ), $plugin_slug );

			return ucwords( $plugin_name );
		}
	}

endif; // end class_exists check.
