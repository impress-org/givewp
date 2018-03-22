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
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Give_Settings_General' ) ) :

	/**
	 * Give_Settings_General.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_General extends Give_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'general';
			$this->label = __( 'General', 'give' );

			$this->default_tab = 'general-settings';

			if( $this->id === give_get_current_setting_tab() ) {
				add_action( 'give_save_settings_give_settings', array( $this, '__give_change_donation_stating_number' ), 10, 3 );
			}

			parent::__construct();
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			$settings        = array();
			$current_section = give_get_current_setting_section();

			switch ( $current_section ) {
				case 'access-control':
					$settings = array(
						// Section 3: Access control.
						array(
							'id'   => 'give_title_session_control_1',
							'type' => 'title',
						),
						array(
							'id'      => 'session_lifetime',
							'name'    => __( 'Session Lifetime', 'give' ),
							'desc'    => __( 'The length of time a user\'s session is kept alive. Give starts a new session per user upon donation. Sessions allow donors to view their donation receipts without being logged in.', 'give' ),
							'type'    => 'select',
							'options' => array(
								'86400'  => __( '24 Hours', 'give' ),
								'172800' => __( '48 Hours', 'give' ),
								'259200' => __( '72 Hours', 'give' ),
								'604800' => __( '1 Week', 'give' ),
							),
						),
						array(
							'id'         => 'limit_display_donations',
							'name'       => __( 'Limit Donations Displayed', 'give' ),
							'desc'       => __( 'Adjusts the number of donations displayed to a non logged-in user when they attempt to access the Donation History page without an active session. For security reasons, it\'s best to leave this at 1-3 donations.', 'give' ),
							'default'    => '1',
							'type'       => 'number',
							'css'        => 'width:50px;',
							'attributes' => array(
								'min' => '1',
								'max' => '10',
							),
						),
						array(
							'name'    => __( 'Email Access', 'give' ),
							'desc'    => __( 'Would you like your donors to be able to access their donation history using only email? Donors whose sessions have expired and do not have an account may still access their donation history via a temporary email access link.', 'give' ),
							'id'      => 'email_access',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							),
						),
						array(
							'name'    => __( 'Enable reCAPTCHA', 'give' ),
							'desc'    => __( 'Would you like to enable the reCAPTCHA feature?', 'give' ),
							'id'      => 'enable_recaptcha',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							),
						),
						array(
							'id'      => 'recaptcha_key',
							'name'    => __( 'reCAPTCHA Site Key', 'give' ),
							/* translators: %s: https://www.google.com/recaptcha/ */
							'desc'    => sprintf( __( 'If you would like to prevent spam on the email access form navigate to <a href="%s" target="_blank">the reCAPTCHA website</a> and sign up for an API key and paste your reCAPTCHA site key here. The reCAPTCHA uses Google\'s user-friendly single click verification method.', 'give' ), esc_url( 'http://docs.givewp.com/recaptcha' ) ),
							'default' => '',
							'type'    => 'text',
						),
						array(
							'id'      => 'recaptcha_secret',
							'name'    => __( 'reCAPTCHA Secret Key', 'give' ),
							'desc'    => __( 'Please paste the reCAPTCHA secret key here from your  reCAPTCHA API Keys panel.', 'give' ),
							'default' => '',
							'type'    => 'text',
						),
						array(
							'name'  => __( 'Access Control Docs Link', 'give' ),
							'id'    => 'access_control_docs_link',
							'url'   => esc_url( 'http://docs.givewp.com/settings-access-control' ),
							'title' => __( 'Access Control', 'give' ),
							'type'  => 'give_docs_link',
						),
						array(
							'id'   => 'give_title_session_control_1',
							'type' => 'sectionend',
						),
					);
					break;

				case 'currency-settings' :
					$currency_position_before = __( 'Before - %s&#x200e;10', 'give' );
					$currency_position_after  = __( 'After - 10%s&#x200f;', 'give' );

					$settings = array(
						// Section 2: Currency
						array(
							'type' => 'title',
							'id'   => 'give_title_general_settings_2',
						),
						array(
							'name' => __( 'Currency Settings', 'give' ),
							'desc' => '',
							'type' => 'give_title',
							'id'   => 'give_title_general_settings_2',
						),
						array(
							'name'    => __( 'Currency', 'give' ),
							'desc'    => __( 'The donation currency. Note that some payment gateways have currency restrictions.', 'give' ),
							'id'      => 'currency',
							'class'   => 'give-select-chosen',
							'type'    => 'select',
							'options' => give_get_currencies(),
							'default' => 'USD',
						),
						array(
							'name'       => __( 'Currency Position', 'give' ),
							'desc'       => __( 'The position of the currency symbol.', 'give' ),
							'id'         => 'currency_position',
							'type'       => 'select',
							'options'    => array(
								/* translators: %s: currency symbol */
								'before' => sprintf( $currency_position_before, give_currency_symbol( give_get_currency() ) ),
								/* translators: %s: currency symbol */
								'after'  => sprintf( $currency_position_after, give_currency_symbol( give_get_currency() ) ),
							),
							'default'    => 'before',
							'attributes' => array(
								'data-before-template' => sprintf( $currency_position_before, '{currency_pos}' ),
								'data-after-template'  => sprintf( $currency_position_after, '{currency_pos}' ),
							),
						),
						array(
							'name'    => __( 'Thousands Separator', 'give' ),
							'desc'    => __( 'The symbol (typically , or .) to separate thousands.', 'give' ),
							'id'      => 'thousands_separator',
							'type'    => 'text',
							'default' => ',',
							'css'     => 'width:12em;',
						),
						array(
							'name'    => __( 'Decimal Separator', 'give' ),
							'desc'    => __( 'The symbol (usually , or .) to separate decimal points.', 'give' ),
							'id'      => 'decimal_separator',
							'type'    => 'text',
							'default' => '.',
							'css'     => 'width:12em;',
						),
						array(
							'name'    => __( 'Number of Decimals', 'give' ),
							'desc'    => __( 'The number of decimal points displayed in amounts.', 'give' ),
							'id'      => 'number_decimals',
							'type'    => 'text',
							'default' => 2,
							'css'     => 'width:12em;',
						),
						array(
							'name'  => __( 'Currency Options Docs Link', 'give' ),
							'id'    => 'currency_settings_docs_link',
							'url'   => esc_url( 'http://docs.givewp.com/settings-currency' ),
							'title' => __( 'Currency Settings', 'give' ),
							'type'  => 'give_docs_link',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'give_title_general_settings_2',
						),
					);

					break;

				case 'general-settings':
					// Get default country code.
					$country = give_get_country();

					// get the list of the states of which default country is selected.
					$states = give_get_states( $country );

					// Get the country list that does not have any states init.
					$no_states_country = give_no_states_country_list();

					$settings = array(
						// Section 1: General.
						array(
							'type' => 'title',
							'id'   => 'give_title_general_settings_1',
						),
						array(
							'name' => __( 'General Settings', 'give' ),
							'desc' => '',
							'type' => 'give_title',
							'id'   => 'give_title_general_settings_1',
						),
						array(
							'name'       => __( 'Success Page', 'give' ),
							/* translators: %s: [give_receipt] */
							'desc'       => sprintf( __( 'The page donors are sent to after completing their donations. The %s shortcode should be on this page.', 'give' ), '<code>[give_receipt]</code>' ),
							'id'         => 'success_page',
							'class'      => 'give-select give-select-chosen',
							'type'       => 'select',
							'options'    => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => 30,
							) ),
							'attributes' => array(
								'data-search-type' => 'pages'
							)
						),
						array(
							'name'       => __( 'Failed Donation Page', 'give' ),
							'desc'       => __( 'The page donors are sent to if their donation is cancelled or fails.', 'give' ),
							'class'      => 'give-select give-select-chosen',
							'id'         => 'failure_page',
							'type'       => 'select',
							'options'    => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => 30,
							) ),
							'attributes' => array(
								'data-search-type' => 'pages'
							)
						),
						array(
							'name'       => __( 'Donation History Page', 'give' ),
							/* translators: %s: [donation_history] */
							'desc'       => sprintf( __( 'The page showing a complete donation history for the current user. The %s shortcode should be on this page.', 'give' ), '<code>[donation_history]</code>' ),
							'id'         => 'history_page',
							'class'      => 'give-select give-select-chosen',
							'type'       => 'select',
							'options'    => give_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => 30,
							) ),
							'attributes' => array(
								'data-search-type' => 'pages'
							)
						),
						array(
							'name'    => __( 'Base Country', 'give' ),
							'desc'    => __( 'The country your site operates from.', 'give' ),
							'id'      => 'base_country',
							'type'    => 'select',
							'options' => give_get_country_list(),
						),
						/**
						 * Add base state to give setting
						 *
						 * @since 1.8.14
						 */
						array(
							'wrapper_class' => ( array_key_exists( $country, $no_states_country ) ? 'give-hidden' : '' ),
							'name'          => __( 'Base State/Province', 'give' ),
							'desc'          => __( 'The state/province your site operates from.', 'give' ),
							'id'            => 'base_state',
							'type'          => ( empty( $states ) ? 'text' : 'select' ),
							'options'       => $states,
						),
						array(
							'name'  => __( 'General Options Docs Link', 'give' ),
							'id'    => 'general_options_docs_link',
							'url'   => esc_url( 'http://docs.givewp.com/settings-general' ),
							'title' => __( 'General Options', 'give' ),
							'type'  => 'give_docs_link',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'give_title_general_settings_1',
						),
					);
					break;

				case 'sequential-ordering':
					$settings = array(
						// Section 4: Sequential donation

						array(
							'id'   => 'give_title_general_settings_4',
							'type' => 'title'
						),
						array(
							'name'                => __( 'Sequential Ordering', 'give' ),
							'id'                  => "{$current_section}_status",
							'desc'                => __( 'Would you like to enable the sequential ordering feature?', 'give' ),
							'type'                => 'radio_inline',
							'default'             => 'enabled',
							'confirm_before_edit' => 'forced',
							'confirmation_msg'    => __( 'Change in this setting can affect you sequential donation numbering. Do you still want to edit this setting?', 'give' ),
							'options'             => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' )
							)
						),
						array(
							'name'                => __( 'Starting Number', 'give' ),
							'id'                  => "{$current_section}_number",
							'type'                => 'number',
							'confirm_before_edit' => 'forced',
							'confirmation_msg'    => __( 'Change in this setting can affect you sequential donation numbering. Do you still want to edit this setting?', 'give' ),
						),
						array(
							'name'                => __( 'Number Prefix', 'give' ),
							'id'                  => "{$current_section}_number_prefix",
							'type'                => 'text',
							'confirm_before_edit' => 'forced',
							'confirmation_msg'    => __( 'Change in this setting can affect you sequential donation numbering. Do you still want to edit this setting?', 'give' ),
						),
						array(
							'name'                => __( 'Number Suffix', 'give' ),
							'id'                  => "{$current_section}_number_suffix",
							'type'                => 'text',
							'confirm_before_edit' => 'forced',
							'confirmation_msg'    => __( 'Change in this setting can affect you sequential donation numbering. Do you still want to edit this setting?', 'give' ),
						),
						array(
							'name'                => __( 'Number Padding', 'give' ),
							'id'                  => "{$current_section}_number_padding",
							'type'                => 'number',
							'default'             => '0',
							'confirm_before_edit' => 'forced',
							'confirmation_msg'    => __( 'Change in this setting can affect you sequential donation numbering. Do you still want to edit this setting?', 'give' ),
						),
						array(
							'name'  => __( 'Sequential Ordering Docs Link', 'give' ),
							'id'    => "{$current_section}_doc link",
							'url'   => esc_url( 'http://docs.givewp.com/settings-sequential-ordering' ),
							'title' => __( 'Sequential Ordering', 'give' ),
							'type'  => 'give_docs_link',
						),
						array(
							'id'   => 'give_title_general_settings_4',
							'type' => 'sectionend'
						)
					);
			}

			/**
			 * Filter the general settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'give_settings_general', $settings );

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
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
				'general-settings'    => __( 'General', 'give' ),
				'currency-settings'   => __( 'Currency', 'give' ),
				'access-control'      => __( 'Access Control', 'give' ),
				'sequential-ordering' => __( 'Sequential Ordering', 'give' ),
			);

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}


		/**
		 * Set flag to reset sequestion donation number starting point when "Sequential Starting Number" value changes
		 *
		 * @since  2.1
		 * @access public
		 *
		 * @param $update_options
		 * @param $option_name
		 * @param $old_options
		 *
		 * @return bool
		 */
		public function __give_change_donation_stating_number( $update_options, $option_name, $old_options ) {
			if ( ! isset( $_POST['sequential-ordering_number'] ) ) {
				return false;
			}

			if ( $update_options['sequential-ordering_number'] !== $old_options['sequential-ordering_number'] ) {
				update_option( '_give_reset_sequential_number', 1 );
			}

			return true;
		}
	}

endif;

return new Give_Settings_General();
