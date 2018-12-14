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
		private $account_url = 'https://givewp.com/my-account/';

		/**
		 * Checkout URL
		 *
		 * @access private
		 * @since  1.7
		 *
		 * @var null|string
		 */
		private $checkout_url = 'https://givewp.com/checkout/';

		/**
		 * Class Constructor
		 *
		 * Set up the Give License Class.
		 *
		 * @access public
		 * @since  1.0
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

			$give_options = give_get_settings();

			$this->file             = $_file;
			$this->item_name        = $_item_name;
			$this->item_shortname   = self::get_short_name( $this->item_name );
			$this->version          = $_version;
			$this->license          = isset( $give_options[ $this->item_shortname . '_license_key' ] ) ? trim( $give_options[ $this->item_shortname . '_license_key' ] ) : '';
			$this->license_data     = __give_get_active_license_info( $this->item_shortname );
			$this->author           = $_author;
			$this->api_url          = is_null( $_api_url ) ? $this->api_url : $_api_url;
			$this->checkout_url     = is_null( $_checkout_url ) ? $this->checkout_url : $_checkout_url;
			$this->account_url      = is_null( $_account_url ) ? $this->account_url : $_account_url;
			$this->auto_updater_obj = null;

			// Add Setting for Give Add-on activation status.
			$is_addon_activated = get_option( 'give_is_addon_activated' );
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
		 * @since  2.1.0
		 * @access public
		 *
		 * @param $plugin_name
		 *
		 * @return string
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
		 * @since  1.0
		 *
		 * @return void
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
		 * @since  1.0
		 *
		 * @return void
		 */
		private function hooks() {

			// Register settings.
			add_filter( 'give_settings_licenses', array( $this, 'settings' ), 1 );

			// Activate license key on settings save.
			add_action( 'admin_init', array( $this, 'activate_license' ), 10 );

			// Deactivate license key.
			add_action( 'admin_init', array( $this, 'deactivate_license' ), 11 );

			// Updater.
			add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );
			add_action( 'admin_notices', array( $this, 'notices' ) );

			// Check license weekly.
			Give_Cron::add_weekly_event( array( $this, 'weekly_license_check' ) );

			// Check subscription weekly.
			Give_Cron::add_weekly_event( array( $this, 'weekly_subscription_check' ) );

			// Show addon notice on plugin page.
			$plugin_name = explode( 'plugins/', $this->file );
			$plugin_name = end( $plugin_name );
			add_action( "after_plugin_row_{$plugin_name}", array( $this, 'plugin_page_notices' ), 10, 3 );

		}


		/**
		 * Auto Updater
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @return void
		 */
		public function auto_updater() {

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
		 * License Settings
		 *
		 * Add license field to settings.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param  array $settings License settings.
		 *
		 * @return array           License settings.
		 */
		public function settings( $settings ) {

			$give_license_settings = array(
				array(
					'name'    => $this->item_name,
					'id'      => $this->item_shortname . '_license_key',
					'desc'    => '',
					'type'    => 'license_key',
					'options' => array(
						'license'      => get_option( $this->item_shortname . '_license_active' ),
						'shortname'    => $this->item_shortname,
						'item_name'    => $this->item_name,
						'api_url'      => $this->api_url,
						'checkout_url' => $this->checkout_url,
						'account_url'  => $this->account_url,
					),
					'size'    => 'regular',
				),
			);

			return array_merge( $settings, $give_license_settings );
		}

		/**
		 * License Settings Content
		 *
		 * Add Some Content to the Licensing Settings.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param  array $settings License settings content.
		 *
		 * @return array           License settings content.
		 */
		public function license_settings_content( $settings ) {

			$give_license_settings = array(
				array(
					'name' => __( 'Add-on Licenses', 'give' ),
					'desc' => '<hr>',
					'type' => 'give_title',
					'id'   => 'give_title',
				),
			);

			return array_merge( $settings, $give_license_settings );
		}

		/**
		 * Activate License
		 *
		 * Activate the license key.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return void
		 */
		public function activate_license() {
			// Bailout.
			if ( ! $this->__is_user_can_edit_license() ) {
				return;
			}

			// Allow third party addon developers to handle license activation.
			if ( $this->__is_third_party_addon() ) {
				do_action( 'give_activate_license', $this );

				return;
			}

			// Delete previous license setting if a empty license key submitted.
			if ( empty( $_POST[ "{$this->item_shortname}_license_key" ] ) ) {
				$this->unset_license();

				return;
			}

			// Do not simultaneously activate add-ons if the user want to deactivate a specific add-on.
			if ( $this->is_deactivating_license() ) {
				return;
			}

			// Check if plugin previously installed.
			if ( $this->is_valid_license() ) {
				return;
			}

			// Get license key.
			$this->license = sanitize_text_field( $_POST[ $this->item_shortname . '_license_key' ] );

			// Delete previous license key from subscription if previously added.
			$this->__remove_license_key_from_subscriptions();

			// Make sure there are no api errors.
			if ( ! ( $license_data = $this->get_license_info( 'activate_license' ) ) ) {
				return;
			}

			// Make sure license is valid.
			// return because admin will want to activate license again.
			if ( ! $this->is_license( $license_data ) ) {
				// Add license key.
				give_update_option( "{$this->item_shortname}_license_key", $this->license );

				return;
			}

			// Tell WordPress to look for updates.
			set_site_transient( 'update_plugins', null );

			// Add license data.
			update_option( "{$this->item_shortname}_license_active", $license_data, false );

			// Add license key.
			give_update_option( "{$this->item_shortname}_license_key", $this->license );

			// Check subscription for license key and store this to db (if any).
			$this->__single_subscription_check();
		}

		/**
		 * Deactivate License
		 *
		 * Deactivate the license key.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return void
		 */
		public function deactivate_license() {
			// Bailout.
			if ( ! $this->__is_user_can_edit_license() ) {
				return;
			}

			// Allow third party add-on developers to handle license deactivation.
			if ( $this->__is_third_party_addon() ) {
				do_action( 'give_deactivate_license', $this );

				return;
			}

			// Run on deactivate button press.
			if ( isset( $_POST[ $this->item_shortname . '_license_key_deactivate' ] ) ) {
				$this->unset_license();
			}
		}

		/**
		 * Check if license key is valid once per week.
		 *
		 * @access public
		 * @since  1.7
		 *
		 * @return void
		 */
		public function weekly_license_check() {

			if (
				! empty( $_POST['give_settings'] ) ||
				empty( $this->license )
			) {
				return;
			}

			// Allow third party add-on developers to handle their license check.
			if ( $this->__is_third_party_addon() ) {
				do_action( 'give_weekly_license_check', $this );

				return;
			}

			// Make sure there are no api errors.
			if ( ! ( $license_data = $this->get_license_info( 'check_license' ) ) ) {
				return;
			}

			// Bailout.
			if ( ! $this->is_license( $license_data ) ) {
				return;
			}

			update_option( $this->item_shortname . '_license_active', $license_data, false );

			return;
		}

		/**
		 * Check subscription validation once per week
		 *
		 * @access public
		 * @since  1.7
		 *
		 * @return void
		 */
		public function weekly_subscription_check() {
			// Bailout.
			if (
				! empty( $_POST['give_settings'] )
				|| empty( $this->license )
			) {
				return;
			}

			// Remove old subscription data.
			if ( absint( get_option( '_give_subscriptions_edit_last', true ) ) < current_time( 'timestamp', 1 ) ) {
				delete_option( 'give_subscriptions' );
				update_option( '_give_subscriptions_edit_last', strtotime( '+ 1 day', current_time( 'timestamp', 1 ) ), false );
			}

			// Allow third party add-on developers to handle their subscription check.
			if ( $this->__is_third_party_addon() ) {
				do_action( 'give_weekly_subscription_check', $this );

				return;
			}

			$this->__single_subscription_check();
		}

		/**
		 * Check if license key is part of subscription or not
		 *
		 * @access private
		 * @since  1.7
		 *
		 * @return void
		 */
		private function __single_subscription_check() {
			if ( empty( $this->license ) ) {
				return;
			}

			/**
			 * Make sure there are no api errors.
			 *
			 * Do not get confused with edd_action check_subscription.
			 * By default edd software licensing api does not have api to check subscription.
			 * This is a custom feature to check subscriptions.
			 */
			$subscription_data = $this->get_license_info( 'check_subscription', true );

			if ( ! empty( $subscription_data['success'] ) && absint( $subscription_data['success'] ) ) {

				$subscriptions = get_option( 'give_subscriptions', array() );

				// Update subscription data only if subscription does not exist already.
				$subscriptions[ $subscription_data['id'] ] = $subscription_data;

				// Initiate default set of license for subscription.
				if ( ! isset( $subscriptions[ $subscription_data['id'] ]['licenses'] ) ) {
					$subscriptions[ $subscription_data['id'] ]['licenses'] = array();
				}

				// Store licenses for subscription.
				if ( ! in_array( $this->license, $subscriptions[ $subscription_data['id'] ]['licenses'] ) ) {
					$subscriptions[ $subscription_data['id'] ]['licenses'][] = $this->license;
				}

				update_option( 'give_subscriptions', $subscriptions, false );
			}
		}

		/**
		 * Admin notices for errors
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return void
		 */
		public function notices() {

			if ( ! current_user_can( 'manage_give_settings' ) ) {
				return;
			}

			// Do not show licenses notices on license tab.
			if ( 'licenses' === give_get_current_setting_tab() ) {
				return;
			}

			static $showed_invalid_message;
			static $showed_subscriptions_message;
			static $addon_license_key_in_subscriptions;

			// Set default value.
			$addon_license_key_in_subscriptions = ! empty( $addon_license_key_in_subscriptions ) ? $addon_license_key_in_subscriptions : array();
			$messages                           = array();

			// Check whether admin has Give Add-on activated since 24 hours?
			$is_license_notice_hidden = Give_Cache::get( 'give_cache_hide_license_notice_after_activation' );

			// Display Invalid License notice, if its more than 24 hours since first Give Add-on activation.
			if (
				empty( $this->license )
				&& empty( $showed_invalid_message )
				&& ( false === $is_license_notice_hidden )
			) {

				Give()->notices->register_notice(
					array(
						'id'               => 'give-invalid-license',
						'type'             => 'error',
						'description'      => sprintf(
							__( 'You have invalid or expired license keys for one or more Give Add-ons. Please go to the <a href="%s">licenses page</a> to correct this issue.', 'give' ),
							admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=licenses' )
						),
						'dismissible_type' => 'user',
						'dismiss_interval' => 'shortly',
					)
				);

				$showed_invalid_message = true;

			}

			// Get subscriptions.
			$subscriptions = get_option( 'give_subscriptions' );

			// Show subscription messages.
			if ( ! empty( $subscriptions ) && ! $showed_subscriptions_message ) {

				foreach ( $subscriptions as $subscription ) {
					// Subscription expires timestamp.
					$subscription_expires = strtotime( $subscription['expires'] );

					// Start showing subscriptions message before one week of renewal date.
					if ( strtotime( '- 7 days', $subscription_expires ) > current_time( 'timestamp', 1 ) ) {
						continue;
					}

					// Check if subscription message already exist in messages.
					if ( array_key_exists( $subscription['id'], $messages ) ) {
						continue;
					}

					// Check if license already expired.
					if ( strtotime( $subscription['expires'] ) < current_time( 'timestamp', 1 ) ) {
						Give()->notices->register_notice(
							array(
								'id'               => "give-expired-subscription-{$subscription['id']}",
								'type'             => 'error',
								'description'      => sprintf(
									__( 'Your Give add-on license expired for payment <a href="%1$s" target="_blank">#%2$d</a>. <a href="%3$s" target="_blank">Click to renew an existing license</a> or %4$s.', 'give' ),
									urldecode( $subscription['invoice_url'] ),
									$subscription['payment_id'],
									"{$this->checkout_url}?edd_license_key={$subscription['license_key']}&utm_campaign=admin&utm_source=licenses&utm_medium=expired",
									Give()->notices->get_dismiss_link(
										array(
											'title' => __( 'Click here if already renewed', 'give' ),
											'dismissible_type' => 'user',
											'dismiss_interval' => 'permanent',
										)
									)
								),
								'dismissible_type' => 'user',
								'dismiss_interval' => 'shortly',
							)
						);
					} else {
						Give()->notices->register_notice(
							array(
								'id'               => "give-expires-subscription-{$subscription['id']}",
								'type'             => 'error',
								'description'      => sprintf(
									__( 'Your Give add-on license will expire in %1$s for payment <a href="%2$s" target="_blank">#%3$d</a>. <a href="%4$s" target="_blank">Click to renew an existing license</a> or %5$s.', 'give' ),
									human_time_diff( current_time( 'timestamp', 1 ), strtotime( $subscription['expires'] ) ),
									urldecode( $subscription['invoice_url'] ),
									$subscription['payment_id'],
									"{$this->checkout_url}?edd_license_key={$subscription['license_key']}&utm_campaign=admin&utm_source=licenses&utm_medium=expired",
									Give()->notices->get_dismiss_link(
										array(
											'title' => __( 'Click here if already renewed', 'give' ),
											'dismissible_type' => 'user',
											'dismiss_interval' => 'permanent',
										)
									)
								),
								'dismissible_type' => 'user',
								'dismiss_interval' => 'shortly',
							)
						);
					}

					// Stop validation for these license keys.
					$addon_license_key_in_subscriptions = array_merge( $addon_license_key_in_subscriptions, $subscription['licenses'] );
				}// End foreach().
				$showed_subscriptions_message = true;
			}// End if().

			// Show Non Subscription Give Add-on messages.
			if (
				! in_array( $this->license, $addon_license_key_in_subscriptions )
				&& ! empty( $this->license )
				&& empty( $showed_invalid_message )
				&& ! $this->is_valid_license()
			) {

				Give()->notices->register_notice(
					array(
						'id'               => 'give-invalid-license',
						'type'             => 'error',
						'description'      => sprintf(
							__( 'You have invalid or expired license keys for one or more Give Add-ons. Please go to the <a href="%s">licenses page</a> to correct this issue.', 'give' ),
							admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=licenses' )
						),
						'dismissible_type' => 'user',
						'dismiss_interval' => 'shortly',
					)
				);

				$showed_invalid_message = true;

			}
		}

		/**
		 * Check if license is valid or not.
		 *
		 * @since  1.7
		 * @access public
		 *
		 * @param null|object $licence_data
		 *
		 * @return bool
		 */
		public function is_valid_license( $licence_data = null ) {
			$license_data = empty( $licence_data ) ? $this->license_data : $licence_data;

			if ( apply_filters( 'give_is_valid_license', ( $this->is_license( $license_data ) && 'valid' === $license_data->license ) ) ) {
				return true;
			}

			return false;
		}


		/**
		 * Check if license is license object of no.
		 *
		 * @since  1.7
		 * @access public
		 *
		 * @param null|object $licence_data
		 *
		 * @return bool
		 */
		public function is_license( $licence_data = null ) {
			$license_data = empty( $licence_data ) ? $this->license_data : $licence_data;

			if ( apply_filters( 'give_is_license', ( is_object( $license_data ) && ! empty( $license_data ) && property_exists( $license_data, 'license' ) ) ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if license is valid or not.
		 *
		 * @access private
		 * @since  1.7
		 *
		 * @return bool
		 */
		private function __is_third_party_addon() {
			return ( false === strpos( $this->api_url, 'givewp.com/' ) );
		}

		/**
		 * Remove license key from subscription.
		 *
		 * This function mainly uses when admin user deactivate license key,
		 * then we do not need subscription information for that license key.
		 *
		 * @access private
		 * @since  1.7
		 *
		 * @return bool
		 */
		private function __remove_license_key_from_subscriptions() {
			$subscriptions = get_option( 'give_subscriptions', array() );

			// Bailout.
			if ( empty( $this->license ) ) {
				return false;
			}

			if ( ! empty( $subscriptions ) ) {
				foreach ( $subscriptions as $subscription_id => $subscription ) {
					$license_index = array_search( $this->license, $subscription['licenses'] );
					if ( false !== $license_index ) {
						// Remove license key.
						unset( $subscriptions[ $subscription_id ]['licenses'][ $license_index ] );

						// Rearrange license keys.
						$subscriptions[ $subscription_id ]['licenses'] = array_values( $subscriptions[ $subscription_id ]['licenses'] );

						// Update subscription information.
						update_option( 'give_subscriptions', $subscriptions, false );
						break;
					}
				}
			}
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
		 * @since  1.8.7
		 * @access public
		 * @return array
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
		 * Check if admin can edit license or not.
		 *
		 * @since  1.8.9
		 * @access private
		 */
		private function __is_user_can_edit_license() {

			// Bailout.
			if (
				! Give_Admin_Settings::verify_nonce()
				|| ! current_user_can( 'manage_give_settings' )
				|| 'licenses' !== give_get_current_setting_tab()
			) {
				return false;
			}

			// Security check.
			if (
				isset( $_POST[ $this->item_shortname . '_license_key-nonce' ] )
				&& ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce' ], $this->item_shortname . '_license_key-nonce' )
			) {
				wp_die( __( 'Nonce verification failed.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
			}

			return true;
		}


		/**
		 * Get license information.
		 *
		 * @since  1.8.9
		 * @access public
		 *
		 * @param string $edd_action
		 * @param bool   $response_in_array
		 *
		 * @return mixed
		 */
		public function get_license_info( $edd_action = '', $response_in_array = false ) {

			if ( empty( $edd_action ) ) {
				return false;
			}

			// Data to send to the API.
			$api_params = array(
				'edd_action' => $edd_action, // never change from "edd_" to "give_"!
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url(),
			);

			// Call the API.
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// Make sure there are no errors.
			if ( is_wp_error( $response ) ) {
				return false;
			}

			return json_decode( wp_remote_retrieve_body( $response ), $response_in_array );
		}


		/**
		 * Unset license
		 *
		 * @since  1.8.14
		 * @access private
		 */
		private function unset_license() {

			// Remove license key from subscriptions if exist.
			$this->__remove_license_key_from_subscriptions();

			// Remove license from database.
			delete_option( "{$this->item_shortname}_license_active" );
			give_delete_option( "{$this->item_shortname}_license_key" );
			unset( $_POST[ "{$this->item_shortname}_license_key" ] );

			// Unset license param.
			$this->license = '';
		}


		/**
		 * Check if deactivating any license key or not.
		 *
		 * @since  1.8.17
		 * @access private
		 *
		 * @return bool
		 */
		private function is_deactivating_license() {
			$status = false;

			foreach ( $_POST as $key => $value ) {
				if ( false !== strpos( $key, 'license_key_deactivate' ) ) {
					$status = true;
					break;
				}
			}

			return $status;
		}

		/**
		 * Return licensed addons info
		 *
		 * Note: note only for internal logic
		 *
		 * @since 2.1.4
		 *
		 * @return array
		 */
		static function get_licensed_addons() {
			return self::$licensed_addons;
		}

	}

endif; // end class_exists check.
