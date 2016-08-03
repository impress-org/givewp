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

		/**
		 * File
		 *
		 * @access private
		 *
		 * @var    string
		 */
		private $file;

		/**
		 * License
		 *
		 * @access private
		 *
		 * @var    string
		 */
		private $license;

		/**
		 * Item name
		 *
		 * @access private
		 *
		 * @var    string
		 */
		private $item_name;

		private $license_data;

		/**
		 * Item shortname
		 *
		 * @access private
		 *
		 * @var    string
		 */
		private $item_shortname;

		/**
		 * Version
		 *
		 * @access private
		 *
		 * @var    string
		 */
		private $version;

		/**
		 * Author
		 *
		 * @access private
		 *
		 * @var    string
		 */
		private $author;

		/**
		 * API URL
		 *
		 * @access private
		 *
		 * @var    string
		 */
		private $api_url = 'https://givewp.com/give-sl-api/';

		/**
		 * Class Constructor
		 *
		 * Set up the Give License Class.
		 *
		 * @access public
		 *
		 * @global array  $give_options
		 *
		 * @param  string $_file
		 * @param  string $_item_name
		 * @param  string $_version
		 * @param  string $_author
		 * @param  string $_optname
		 * @param  string $_api_url
		 *
		 * @return void
		 */
		public function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null ) {
			global $give_options;

			$this->file           = $_file;
			$this->item_name      = $_item_name;
			$this->item_shortname = 'give_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
			$this->version        = $_version;
			$this->license        = isset( $give_options[ $this->item_shortname . '_license_key' ] ) ? trim( $give_options[ $this->item_shortname . '_license_key' ] ) : '';
			$this->license_data   = get_option( $this->item_shortname . '_license_active' );
            $this->author         = $_author;
			$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

			// Setup hooks
			$this->includes();
			$this->hooks();
			//$this->auto_updater();
		}

		/**
		 * Includes
		 *
		 * Include the updater class.
		 *
		 * @access private
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
		 *
		 * @return void
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

            // Check license weekly.
            add_action( 'give_weekly_scheduled_events', array( $this, 'weekly_license_check' ) );
        }

		/**
		 * Auto Updater
		 *
		 * @access  private
		 * @global  array $give_options
		 * @return  bool
		 */
		public function auto_updater() {

			if ( ! $this->is_valid_license() ) {
				return false;
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
		 * License Settings
		 *
		 * Add license field to settings.
		 *
		 * @access public
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
					    'license'   => get_option( $this->item_shortname . '_license_active' ),
                        'shortname' => $this->item_shortname,
                        'item_name' => $this->item_name
                    ),
					'size'    => 'regular'
				)
			);

			return array_merge( $settings, $give_license_settings );
		}

		/**
		 * License Settings Content
		 *
		 * Add Some Content to the Licensing Settings.
		 *
		 * @access public
		 *
		 * @param  array $settings License settings content.
		 *
		 * @return array           License settings content.
		 */
		public function license_settings_content( $settings ) {

			$give_license_settings = array(
				array(
					'name' => esc_html__( 'Add-on Licenses', 'give' ),
					'desc' => '<hr>',
					'type' => 'give_title',
					'id'   => 'give_title'
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
		 *
		 * @return void
		 */
		public function activate_license() {
            // Bailout: Check if license key set of not.
			if ( ! isset( $_POST[ $this->item_shortname . '_license_key' ] ) ) {
				return;
			}

			// Security check.
			if ( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce' ], $this->item_shortname . '_license_key-nonce' ) ) {

				wp_die( esc_html__( 'Nonce verification failed.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );

			}

			// Check if user have correct permissions.
            if ( ! current_user_can( 'manage_give_settings' ) ) {
                return;
            }

            // Delete previous license setting if a empty license key submitted.
            if ( empty( $_POST[ $this->item_shortname . '_license_key' ] ) ) {
                give_delete_option( $this->item_shortname . '_license_active' );
                return;
            }

            // Do not simultaneously activate any addon if user want to deactivate any addon.
            foreach ( $_POST as $key => $value ) {
                if ( false !== strpos( $key, 'license_key_deactivate' ) ) {
                    // Don't activate a key when deactivating a different key
                    return;
                }
            }


            // Check if plugin previously installed.
            if ( $this->is_valid_license() ) {
                return;
            }

            // Get license key.
            $license = sanitize_text_field( $_POST[ $this->item_shortname . '_license_key' ] );

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
            update_option( $this->item_shortname . '_license_active', $license_data );
		}

		/**
		 * Deactivate License
		 *
		 * Deactivate the license key.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function deactivate_license() {

			if ( ! isset( $_POST[ $this->item_shortname . '_license_key' ] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce' ], $this->item_shortname . '_license_key-nonce' ) ) {

				wp_die( esc_html__( 'Nonce verification failed.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );

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


                // Remove license data.
				delete_option( $this->item_shortname . '_license_active' );
			}
		}


        /**
         * Check if license key is valid once per week
         *
         * @access  public
         * @since   1.6
         * @return  bool/void
         */
        public function weekly_license_check() {

            if( ! empty( $_POST['give_settings'] ) ) {
                // Don't fire when saving settings
                return false;
            }

            if( empty( $this->license ) ) {
                return false;
            }

            // Data to send in our API request.
            $api_params = array(
                'edd_action'=> 'check_license',
                'license' 	=> $this->license,
                'item_name' => urlencode( $this->item_name ),
                'url'       => home_url()
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

            // Make sure the response came back okay.
            if ( is_wp_error( $response ) ) {
                return false;
            }

            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
            update_option( $this->item_shortname . '_license_active', $license_data );
        }

        /**
         * Admin notices for errors
         *
         * @access  public
         * @return  void
         */
        public function notices() {
            static $showed_invalid_message;

            if( empty( $this->license ) ) {
                return;
            }

            if( ! current_user_can( 'manage_shop_settings' ) ) {
                return;
            }

            $messages = array();

            if( ! $this->is_valid_license() && empty( $showed_invalid_message ) ) {

                if( empty( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {
                    $messages[] = sprintf(
                        __( 'You have invalid or expired license keys for Give Addon. Please go to the <a href="%s">Licenses page</a> to correct this issue.', 'give' ),
                        admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=licenses' )
                    );
                    $showed_invalid_message = true;
                }

            }


            if( ! empty( $messages ) ) {
                foreach( $messages as $message ) {
                    echo '<div class="notice notice-error is-dismissible give-license-notice">';
                    echo '<p>' . $message . '</p>';
                    echo '</div>';
                }
            }
        }


        /**
         * Check if license is valid or not.
         * @return bool
         */
		public function is_valid_license() {
            if( apply_filters( 'give_is_valid_license' , ( is_object( $this->license_data ) && ! empty( $this->license_data ) && 'valid' === $this->license_data->license ) ) ) {
                return true;
            }

            return false;
        }
	}

endif; // end class_exists check
