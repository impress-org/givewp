<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_General
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_General' ) ) :

	/**
	 * Give_Settings_General.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_General extends Give_Settings_Page {

		/**
		 * Setting page id.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $label = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'general';
			$this->label = esc_html__( 'General', 'give' );

			add_filter( 'give_default_setting_tab_section_general', array( $this, 'set_default_setting_tab' ), 10 );
			add_filter( 'give_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( "give_sections_{$this->id}_page", array( $this, 'output_sections' ) );
			add_action( "give_settings_{$this->id}_page", array( $this, 'output' ) );
			add_action( "give_settings_save_{$this->id}", array( $this, 'save' ) );
		}

		/**
		 * Default setting tab.
		 *
		 * @since  1.8
		 * @param  $setting_tab
		 * @return string
		 */
		function set_default_setting_tab( $setting_tab ) {
			return 'general-settings';
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8
		 * @param  array $pages Lst of pages.
		 * @return array
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			$settings = array();
			$current_section = give_get_current_setting_section();

			switch ( $current_section ) {
				case 'access-control':
					$settings = array(
						// Section 3: Access control.
						array(
							'id'   => 'give_title_session_control_1',
							'type' => 'title'
						),
						array(
							'id'      => 'session_lifetime',
							'name'    => esc_html__( 'Session Lifetime', 'give' ),
							'desc'    => esc_html__( 'The length of time a user\'s session is kept alive. Give starts a new session per user upon donation. Sessions allow donors to view their donation receipts without being logged in.', 'give' ),
							'type'    => 'select',
							'options' => array(
								'86400'  => esc_html__( '24 Hours', 'give' ),
								'172800' => esc_html__( '48 Hours', 'give' ),
								'259200' => esc_html__( '72 Hours', 'give' ),
								'604800' => esc_html__( '1 Week', 'give' ),
							)
						),
						array(
							'name' => esc_html__( 'Email Access', 'give' ),
							'desc' => esc_html__( 'Would you like your donors to be able to access their donation history using only email? Donors whose sessions have expired and do not have an account may still access their donation history via a temporary email access link.', 'give' ),
							'id'   => 'email_access',
							'type' => 'checkbox',
						),
						array(
							'id'      => 'recaptcha_key',
							'name'    => esc_html__( 'reCAPTCHA Site Key', 'give' ),
							/* translators: %s: https://www.google.com/recaptcha/ */
							'desc'    => sprintf( __( 'If you would like to prevent spam on the email access form navigate to <a href="%s" target="_blank">the reCAPTCHA website</a> and sign up for an API key. The reCAPTCHA uses Google\'s user-friendly single click verification method.', 'give' ), esc_url( 'https://www.google.com/recaptcha/' ) ),
							'default' => '',
							'type'    => 'text'
						),
						array(
							'id'      => 'recaptcha_secret',
							'name'    => esc_html__( 'reCAPTCHA Secret Key', 'give' ),
							'desc'    => esc_html__( 'Please paste the reCAPTCHA secret key here from your manage reCAPTCHA API Keys panel.', 'give' ),
							'default' => '',
							'type'    => 'text'
						),
						array(
							'id'   => 'give_title_session_control_1',
							'type' => 'sectionend'
						),
					);
					break;

				case 'currency-settings' :
					$settings = array(
						// Section 2: Currency
						array(
							'type' => 'title',
							'id'   => 'give_title_general_settings_2'
						),
						array(
							'name' => esc_html__( 'Currency Settings', 'give' ),
							'desc' => '',
							'type' => 'give_title',
							'id'   => 'give_title_general_settings_2'
						),
						array(
							'name'    => esc_html__( 'Currency', 'give' ),
							'desc'    => esc_html__( 'The donation currency. Note that some payment gateways have currency restrictions.', 'give' ),
							'id'      => 'currency',
							'type'    => 'select',
							'options' => give_get_currencies(),
							'default' => 'USD',
						),
						array(
							'name'    => esc_html__( 'Currency Position', 'give' ),
							'desc'    => esc_html__( 'The position of the currency symbol.', 'give' ),
							'id'      => 'currency_position',
							'type'    => 'select',
							'options' => array(
								/* translators: %s: currency symbol */
								'before' => sprintf( esc_html__( 'Before - %s10', 'give' ), give_currency_symbol( give_get_currency() ) ),
								/* translators: %s: currency symbol */
								'after'  => sprintf( esc_html__( 'After - 10%s', 'give' ), give_currency_symbol( give_get_currency() ) )
							),
							'default' => 'before',
						),
						array(
							'name'            => esc_html__( 'Thousands Separator', 'give' ),
							'desc'            => esc_html__( 'The symbol (typically , or .) to separate thousands.', 'give' ),
							'id'              => 'thousands_separator',
							'type'            => 'text',
							'default'         => ',',
							'css'             => 'width:12em;',
						),
						array(
							'name'    => esc_html__( 'Decimal Separator', 'give' ),
							'desc'    => esc_html__( 'The symbol (usually , or .) to separate decimal points.', 'give' ),
							'id'      => 'decimal_separator',
							'type'    => 'text',
							'default' => '.',
							'css'             => 'width:12em;',
						),
						array(
							'name'            => __( 'Number of Decimals', 'give' ),
							'desc'            => __( 'The number of decimal points displayed in amounts.', 'give' ),
							'id'              => 'number_decimals',
							'type'            => 'text',
							'default'         => 2,
							'sanitization_cb' => 'give_sanitize_number_decimals',
							'css'             => 'width:12em;',
						),
						array(
							'type' => 'title',
							'id'   => 'give_title_general_settings_2'
						)
					);
					break;

				case 'general-settings':
					$settings = array(
						// Section 1: General.
						array(
							'type' => 'title',
							'id'   => 'give_title_general_settings_1'
						),
						array(
							'name' => esc_html__( 'General Settings', 'give' ),
							'desc' => '',
							'type' => 'give_title',
							'id'   => 'give_title_general_settings_1'
						),
						array(
							'name'    => esc_html__( 'Success Page', 'give' ),
							/* translators: %s: [give_receipt] */
							'desc'    => sprintf( __( 'The page donors are sent to after completing their donations. The %s shortcode should be on this page.', 'give' ), '<code>[give_receipt]</code>' ),
							'id'      => 'success_page',
							'type'    => 'select',
							'options' => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => - 1
							) ),
						),
						array(
							'name'    => esc_html__( 'Failed Donation Page', 'give' ),
							'desc'    => esc_html__( 'The page donors are sent to if their donation is cancelled or fails.', 'give' ),
							'id'      => 'failure_page',
							'type'    => 'select',
							'options' => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => - 1
							) ),
						),
						array(
							'name'    => esc_html__( 'Donation History Page', 'give' ),
							/* translators: %s: [donation_history] */
							'desc'    => sprintf( __( 'The page showing a complete donation history for the current user. The %s shortcode should be on this page.', 'give' ), '<code>[donation_history]</code>' ),
							'id'      => 'history_page',
							'type'    => 'select',
							'options' => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => - 1
							) ),
						),
						array(
							'name'    => esc_html__( 'Base Country', 'give' ),
							'desc'    => esc_html__( 'The country your site operates from.', 'give' ),
							'id'      => 'base_country',
							'type'    => 'select',
							'options' => give_get_country_list(),
						),
						array(
							'type' => 'sectionend',
							'id'   => 'give_title_general_settings_1'
						)
					);
					break;

				default:
					/**
					 * Filter the general settings.
					 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
					 */
					$settings = apply_filters( 'give_settings_general', $settings );
			}

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 * @param  array $settings
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Get sections.
		 *
		 * @since 1.8
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'general-settings'  => esc_html__( 'General', 'give' ),
				'currency-settings' => esc_html__( 'Currency', 'give' ),
				'access-control'    => esc_html__( 'Access Control', 'give' )
			);

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output sections.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output_sections() {
			// Get current section.
			$current_section = give_get_current_setting_section();

			// Get all sections.
			$sections = $this->get_sections();

			if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
				return;
			}

			echo '<ul class="subsubsub">';

				// Get section keys.
				$array_keys = array_keys( $sections );

				foreach ( $sections as $id => $label ) {
					echo '<li><a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
				}

			echo '</ul><br class="clear" />';
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output() {
			$settings = $this->get_settings();

			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}

		/**
		 * Save settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function save() {
			$settings = $this->get_settings();

			Give_Admin_Settings::save_fields( $settings, 'give_settings' );
		}
	}

endif;

return new Give_Settings_General();
