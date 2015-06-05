<?php
/**
 * Give License handler
 *
 * This class simplifies the process of adding license information to new Give add-ons.
 *
 * @version 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_License' ) ) :

	/**
	 * Give_License Class
	 */
	class Give_License {
		private $file;
		private $license;
		private $item_name;
		private $item_shortname;
		private $version;
		private $author;
		private $api_url = 'https://givewp.com/give-sl-api/';

		/**
		 * Class constructor
		 *
		 * @global  array $give_options
		 *
		 * @param string  $_file
		 * @param string  $_item_name
		 * @param string  $_version
		 * @param string  $_author
		 * @param string  $_optname
		 * @param string  $_api_url
		 */
		public function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null ) {
			global $give_options;

			$this->file           = $_file;
			$this->item_name      = $_item_name;
			$this->item_shortname = 'give_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
			$this->version        = $_version;
			$this->license        = isset( $give_options[ $this->item_shortname . '_license_key' ] ) ? trim( $give_options[ $this->item_shortname . '_license_key' ] ) : '';
			$this->author         = $_author;
			$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;


			// Setup hooks
			$this->includes();
			$this->hooks();
			//$this->auto_updater();
		}

		/**
		 * Include the updater class
		 *
		 * @access  private
		 * @return  void
		 */
		private function includes() {
			if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
				require_once 'admin/EDD_SL_Plugin_Updater.php';
			}
		}

		/**
		 * Setup hooks
		 *
		 * @access  private
		 * @return  void
		 */
		private function hooks() {

			// Register settings
			add_filter( 'give_settings_licenses', array( $this, 'settings' ), 1 );

			// Activate license key on settings save
			add_action( 'admin_init', array( $this, 'activate_license' ) );

			// Deactivate license key
			add_action( 'admin_init', array( $this, 'deactivate_license' ) );

			// Updater
			add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );

			add_action( 'admin_notices', array( $this, 'notices' ) );
		}

		/**
		 * Auto updater
		 *
		 * @access  private
		 * @global  array $give_options
		 * @return  void
		 */
		public function auto_updater() {

			if ( 'valid' !== get_option( $this->item_shortname . '_license_active' ) ) {
				return;
			}

			// Setup the updater
			$give_updater = new EDD_SL_Plugin_Updater(
				$this->api_url,
				$this->file,
				array(
					'version'   => $this->version,
					'license'   => $this->license,
					'item_name' => $this->item_name,
					'author'    => $this->author
				)
			);
		}


		/**
		 * Add license field to settings
		 *
		 * @access  public
		 *
		 * @param array $settings
		 *
		 * @return  array
		 */
		public function settings( $settings ) {

			$give_license_settings = array(
				array(
					'name'    => sprintf( __( '%1$s', 'give' ), $this->item_name ),
					'id'      => $this->item_shortname . '_license_key',
					'desc'    => '',
					'type'    => 'license_key',
					'options' => array( 'is_valid_license_option' => $this->item_shortname . '_license_active' ),
					'size'    => 'regular'
				)
			);

			return array_merge( $settings, $give_license_settings );
		}

		/**
		 * Add Some Content to the Licensing Settings
		 *
		 * @access  public
		 *
		 * @param array $settings
		 *
		 * @return  array
		 */
		public function license_settings_content( $settings ) {

			$give_license_settings = array(
				array(
					'name' => __( 'Add-on Licenses', 'give' ),
					'desc' => '<hr>',
					'type' => 'give_title',
					'id'   => 'give_title'
				),
			);

			return array_merge( $settings, $give_license_settings );
		}


		/**
		 * Activate the license key
		 *
		 * @access  public
		 * @return  void
		 */
		public function activate_license() {

			if ( ! isset( $_POST[ $this->item_shortname . '_license_key' ] ) ) {
				return;
			}

			foreach ( $_POST as $key => $value ) {
				if ( false !== strpos( $key, 'license_key_deactivate' ) ) {
					// Don't activate a key when deactivating a different key
					return;
				}
			}

			if ( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce' ], $this->item_shortname . '_license_key-nonce' ) ) {

				wp_die( __( 'Nonce verification failed', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );

			}

			if ( ! current_user_can( 'manage_give_settings' ) ) {
				return;
			}

			if ( 'valid' === get_option( $this->item_shortname . '_license_active' ) ) {
				return;
			}

			$license = sanitize_text_field( $_POST[ $this->item_shortname . '_license_key' ] );

			if ( empty( $license ) ) {
				return;
			}

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'activate_license', //never change from "edd_" to "give_"!
				'license'    => $license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				return;
			}

			// Tell WordPress to look for updates
			set_site_transient( 'update_plugins', null );

			// Decode license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			update_option( $this->item_shortname . '_license_active', $license_data->license );

			if ( ! (bool) $license_data->success ) {
				set_transient( 'give_license_error', $license_data, 1000 );
			} else {
				delete_transient( 'give_license_error' );
			}
		}


		/**
		 * Deactivate the license key
		 *
		 * @access  public
		 * @return  void
		 */
		public function deactivate_license() {

			if ( ! isset( $_POST[ $this->item_shortname . '_license_key' ] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce' ], $this->item_shortname . '_license_key-nonce' ) ) {

				wp_die( __( 'Nonce verification failed', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );

			}

			if ( ! current_user_can( 'manage_give_settings' ) ) {
				return;
			}

			// Run on deactivate button press
			if ( isset( $_POST[ $this->item_shortname . '_license_key_deactivate' ] ) ) {

				// Data to send to the API
				$api_params = array(
					'edd_action' => 'deactivate_license', //never change from "edd_" to "give_"!
					'license'    => $this->license,
					'item_name'  => urlencode( $this->item_name ),
					'url'        => home_url()
				);

				// Call the API
				$response = wp_remote_post(
					$this->api_url,
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'body'      => $api_params
					)
				);


				// Make sure there are no errors
				if ( is_wp_error( $response ) ) {
					return;
				}

				// Decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				delete_option( $this->item_shortname . '_license_active' );

				if ( ! (bool) $license_data->success ) {
					set_transient( 'give_license_error', $license_data, 1000 );
				} else {
					delete_transient( 'give_license_error' );
				}
			}
		}


		/**
		 * Admin notices for errors
		 *
		 * @access  public
		 * @return  void
		 */
		public function notices() {

			if ( ! isset( $_GET['page'] ) || 'give-settings' !== $_GET['page'] ) {
				return;
			}

			if ( ! isset( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {
				return;
			}

			$license_error = get_transient( 'give_license_error' );

			if ( false === $license_error ) {
				return;
			}

			if ( ! empty( $license_error->error ) ) {

				switch ( $license_error->error ) {

					case 'item_name_mismatch' :

						$message = __( 'This license does not belong to the product you have entered it for.', 'give' );
						break;

					case 'no_activations_left' :

						$message = __( 'This license does not have any activations left', 'give' );
						break;

					case 'expired' :

						$message = __( 'This license key is expired. Please renew it.', 'give' );
						break;

					default :

						$message = sprintf( __( 'There was a problem activating your license key, please try again or contact support. Error code: %s', 'give' ), $license_error->error );
						break;

				}

			}

			if ( ! empty( $message ) ) {

				echo '<div class="error">';
				echo '<p>' . $message . '</p>';
				echo '</div>';

			}

			delete_transient( 'give_license_error' );

		}
	}

endif; // end class_exists check
