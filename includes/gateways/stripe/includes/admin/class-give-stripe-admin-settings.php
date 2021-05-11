<?php
/**
 * Give - Stripe Core Admin Settings
 *
 * @since 2.5.0
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Stripe_Admin_Settings' ) ) {
	/**
	 * Class Give_Stripe_Admin_Settings
	 *
	 * @since 2.5.0
	 */
	class Give_Stripe_Admin_Settings {

		/**
		 * Single Instance.
		 *
		 * @since  2.5.0
		 * @access private
		 *
		 * @var Give_Stripe_Admin_Settings $instance
		 */
		private static $instance;

		/**
		 * Section ID.
		 *
		 * @since  2.5.0
		 * @access private
		 *
		 * @var string $section_id
		 */
		private $section_id;

		/**
		 * Section Label.
		 *
		 * @since  2.5.0
		 * @access private
		 *
		 * @var string $section_label
		 */
		private $section_label;

		/**
		 * Give_Stripe_Admin_Settings() constructor.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function __construct() {

			$this->section_id    = 'stripe';
			$this->section_label = __( 'Stripe', 'give' );

			// Bailout, if not accessed via admin.
			if ( ! is_admin() ) {
				return;
			}

			add_filter( 'give_get_sections_gateways', [ $this, 'register_sections' ], 1 );
			add_filter( 'give_get_groups_stripe-settings', [ $this, 'register_groups' ] );
			add_filter( 'give_get_settings_gateways', [ $this, 'register_settings' ] );
			add_filter( 'give_get_sections_advanced', [ $this, 'register_advanced_sections' ] );
			add_filter( 'give_get_settings_advanced', [ $this, 'register_advanced_settings' ], 10, 1 );
			add_action( 'give_admin_field_stripe_account_manager', [ $this, 'stripe_account_manager_field' ], 10, 2 );
			add_action( 'give_admin_field_stripe_webhooks', [ $this, 'stripe_webhook_field' ], 10, 2 );
			add_action( 'give_admin_field_stripe_styles_field', [ $this, 'stripe_styles_field' ], 10, 2 );
			add_action( 'give_disconnect_connected_stripe_account', [ $this, 'disconnect_connected_stripe_account' ] );
		}

		/**
		 * Register sections.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @param array $sections List of sections.
		 *
		 * @return array
		 */
		public function register_sections( $sections ) {
			$sections['stripe-settings'] = __( 'Stripe', 'give' );

			return $sections;
		}

		/**
		 * Register groups of a section.
		 *
		 * @since  2.6.0
		 * @access public
		 *
		 * @return array
		 */
		public function register_groups() {

			$groups = [
				'accounts'    => __( 'Manage Accounts', 'give' ),
				'general'     => __( 'General Settings', 'give' ),
				'credit-card' => __( 'Credit Card On Site', 'give' ),
				'checkout'    => __( 'Stripe Checkout', 'give' ),
				'sepa'        => __( 'SEPA Direct Debit', 'give' ),
				'becs'        => __( 'BECS Direct Debit', 'give' ),
			];

			return apply_filters( 'give_stripe_register_groups', $groups );
		}

		/**
		 * Add "Stripe" advanced settings.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @param array $section List of sections.
		 *
		 * @return mixed
		 */
		public function register_advanced_sections( $section ) {
			$section['stripe'] = __( 'Stripe', 'give' );

			return $section;
		}

		/**
		 * Register Stripe Main Settings.
		 *
		 * @param array $settings List of setting fields.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return array
		 */
		public function register_settings( $settings ) {

			$section = give_get_current_setting_section();

			switch ( $section ) {

				case 'stripe-settings':
					// Manage Accounts admin fields.
					$settings['accounts'][] = [
						'id'   => 'give_title_stripe_accounts',
						'type' => 'title',
					];

					$settings['accounts'][] = [
						'name'          => esc_html__( 'Stripe Connect', 'give' ),
						'desc'          => '',
						'wrapper_class' => 'give-stripe-account-manager-wrap js-fields-has-custom-saving-logic',
						'id'            => 'stripe_account_manager',
						'type'          => 'stripe_account_manager',
					];

					$settings['accounts'][] = [
						'id'   => 'give_title_stripe_accounts',
						'type' => 'sectionend',
					];

					// Stripe Admin Settings - Header.
					$settings['general'][] = [
						'id'   => 'give_title_stripe_general',
						'type' => 'title',
					];

					/**
					 * This filter hook is used to add configuration fields like api key, access token, oAuth button, etc.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_configuration_fields', $settings );

					$settings['general'][] = [
						'name'          => esc_html__( 'Stripe Webhooks', 'give' ),
						'desc'          => '',
						'wrapper_class' => 'give-stripe-webhooks-tr',
						'id'            => 'stripe_webhooks',
						'type'          => 'stripe_webhooks',
					];

					$settings['general'][] = [
						'name'       => esc_html__( 'Statement Descriptor', 'give' ),
						'desc'       => __( 'This is the text that appears on your donor\'s bank statements. Statement descriptors are limited to 22 characters, cannot use the special characters <code><</code>, <code>></code>, <code>\'</code>, or <code>"</code>, and must not consist solely of numbers. This is typically the name of your website or organization.', 'give' ),
						'id'         => 'stripe_statement_descriptor',
						'type'       => 'text',
						'attributes' => [
							'maxlength'   => '22',
							'placeholder' => get_bloginfo( 'name' ),
						],
						'default'    => get_bloginfo( 'name' ),
					];

					$settings['general'][] = [
						'name' => esc_html__( 'Collect Billing Details', 'give' ),
						'desc' => esc_html__( 'This option enables the billing details section for Stripe, requiring the donor\'s address to complete the donation. These fields are not required by Stripe to process the transaction, but you may have the need to collect the data.', 'give' ),
						'id'   => 'stripe_collect_billing',
						'type' => 'checkbox',
					];

					/**
					 * This filter hook is used to add fields after Stripe General fields.
					 *
					 * @since 2.5.5
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_after_general_fields', $settings );

					$settings['general'][] = [
						'name' => esc_html__( 'Stripe Receipt Emails', 'give' ),
						'desc' => sprintf(
							/* translators: 1. GiveWP Support URL */
							__( 'Check this option if you would like donors to receive receipt emails directly from Stripe. By default, donors will receive GiveWP generated <a href="%1$s" target="_blank">receipt emails</a>. Checking this option does not disable GiveWP emails.', 'give' ),
							admin_url( '/edit.php?post_type=give_forms&page=give-settings&tab=emails' )
						),
						'id'   => 'stripe_receipt_emails',
						'type' => 'checkbox',
					];

					$settings['general'][] = [
						'name'  => esc_html__( 'Stripe Gateway Documentation', 'give' ),
						'id'    => 'display_settings_general_docs_link',
						'url'   => esc_url( 'http://docs.givewp.com/addon-stripe' ),
						'title' => esc_html__( 'Stripe Gateway Documentation', 'give' ),
						'type'  => 'give_docs_link',
					];

					// Stripe Admin Settings - Footer.
					$settings['general'][] = [
						'id'   => 'give_title_stripe_general',
						'type' => 'sectionend',
					];

					// Stripe Admin Settings - Header.
					$settings['credit-card'][] = [
						'id'   => 'give_title_stripe_credit_card',
						'type' => 'title',
					];

					/**
					 * This filter hook is used to add fields before Stripe Credit Card fields.
					 *
					 * @since 2.5.5
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_before_credit_card_fields', $settings );

					$settings['credit-card'][] = [
						'name'          => esc_html__( 'Credit Card Fields Format', 'give' ),
						'desc'          => esc_html__( 'This option allows you to show single or multiple credit card fields on your donation forms.', 'give' ),
						'id'            => 'stripe_cc_fields_format',
						'wrapper_class' => 'stripe-cc-field-format-settings',
						'type'          => 'radio_inline',
						'default'       => 'multi',
						'options'       => [
							'single' => esc_html__( 'Single Field', 'give' ),
							'multi'  => esc_html__( 'Multi Field', 'give' ),
						],
					];

					/**
					 * This filter hook is used to add fields after Stripe Credit Card fields.
					 *
					 * @since 2.5.5
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_after_credit_card_fields', $settings );

					$settings['credit-card'][] = [
						'name'  => esc_html__( 'Stripe Gateway Documentation', 'give' ),
						'id'    => 'display_settings_credit_card_docs_link',
						'url'   => esc_url( 'http://docs.givewp.com/stripe-ccfields' ),
						'title' => __( 'Stripe Gateway Documentation', 'give' ),
						'type'  => 'give_docs_link',
					];

					// Stripe Admin Settings - Footer.
					$settings['credit-card'][] = [
						'id'   => 'give_title_stripe_credit_card',
						'type' => 'sectionend',
					];

					/**
					 * This filter hook is used to add fields before Stripe Checkout fields.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_before_checkout_fields', $settings );

					// Checkout.
					$settings['checkout'][] = [
						'id'   => 'give_title_stripe_checkout',
						'type' => 'title',
					];

					$settings['checkout'][] = [
						'name'          => esc_html__( 'Checkout Type', 'give' ),
						'desc'          => sprintf(
							'%1$s <a href="%2$s" target="_blank">%3$s</a> %4$s',
							esc_html__( 'This option allows you to select from the two types of Stripe Checkout methods available for processing donations. The "Modal" option uses Stripe elements in a popup modal which does not take the donor off your website. The "Redirect" option uses Stripe\'s new off-site', 'give' ),
							esc_url( 'https://stripe.com/docs/payments/checkout' ),
							esc_html__( 'Checkout', 'give' ),
							esc_html__( 'interface that provides donors an easy way to pay with Credit or Debit Cards, Apple, and Google Pay.', 'give' )
						),
						'id'            => 'stripe_checkout_type',
						'wrapper_class' => 'stripe-checkout-type',
						'type'          => 'radio_inline',
						'default'       => 'modal',
						'options'       => [
							'modal'    => __( 'Modal (Stripe Elements)', 'give' ),
							'redirect' => __( 'Redirect (Checkout 2.0)', 'give' ),
						],
					];

					$settings['checkout'][] = [
						'name'          => esc_html__( 'Checkout Heading', 'give' ),
						'desc'          => esc_html__( 'This is the main heading within the modal checkout. Typically, this is the name of your organization, cause, or website.', 'give' ),
						'id'            => 'stripe_checkout_name',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => get_bloginfo( 'name' ),
						'type'          => 'text',
					];

					$settings['checkout'][] = [
						'name'          => esc_html__( 'Background Image', 'give' ),
						'desc'          => esc_html__( 'This background image appears in the header section of the Stripe checkout modal window and provides better brand recognition that leads to increased conversion rates. The recommended minimum size of background image is 400x200px. The supported image types are: .gif, .jpeg, and .png.', 'give' ),
						'id'            => 'stripe_checkout_background_image',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'type'          => 'file',
						'options'       => [
							'url' => false, // Hide the text input for the url.
						],
						'text'          => [
							'add_upload_file_text' => esc_html__( 'Add or Upload Image', 'give' ),
						],
					];

					/**
					 * This filter hook is used to add fields after Stripe Checkout fields.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_after_checkout_fields', $settings );

					$settings['checkout'][] = [
						'name'  => esc_html__( 'Stripe Gateway Documentation', 'give' ),
						'id'    => 'display_settings_checkout_docs_link',
						'url'   => esc_url( 'http://docs.givewp.com/settings-stripe-checkout' ),
						'title' => __( 'Stripe Gateway Documentation', 'give' ),
						'type'  => 'give_docs_link',
					];

					// Stripe Admin Settings - Footer.
					$settings['checkout'][] = [
						'id'   => 'give_title_stripe_checkout',
						'type' => 'sectionend',
					];

					// SEPA Direct Debit.
					$settings['sepa'][] = [
						'id'   => 'give_title_stripe_sepa',
						'type' => 'title',
					];

					$settings['sepa'][] = [
						'name'          => esc_html__( 'Display Icon', 'give' ),
						'desc'          => esc_html__( 'This option allows you to display a bank building icon within the IBAN input field for SEPA Direct Debit.', 'give' ),
						'id'            => 'stripe_hide_icon',
						'wrapper_class' => 'stripe-hide-icon',
						'type'          => 'radio_inline',
						'default'       => 'enabled',
						'options'       => [
							'enabled'  => esc_html__( 'Enabled', 'give' ),
							'disabled' => esc_html__( 'Disabled', 'give' ),
						],
					];

					$is_hide_icon = give_is_setting_enabled( give_get_option( 'stripe_hide_icon', 'enabled' ) );

					$settings['sepa'][] = [
						'name'          => esc_html__( 'Icon Style', 'give' ),
						'desc'          => esc_html__( 'This option allows you to select the icon style for the IBAN element of SEPA Direct Debit.', 'give' ),
						'id'            => 'stripe_icon_style',
						'wrapper_class' => $is_hide_icon ? 'stripe-icon-style' : 'stripe-icon-style give-hidden',
						'type'          => 'radio_inline',
						'default'       => 'default',
						'options'       => [
							'default' => esc_html__( 'Default', 'give' ),
							'solid'   => esc_html__( 'Solid', 'give' ),
						],
					];

					$settings['sepa'][] = [
						'name'          => esc_html__( 'Display Mandate Acceptance', 'give' ),
						'desc'          => esc_html__( 'The mandate acceptance text explains to donors how the payment processing will work for their donation. The text will display below the IBAN field.', 'give' ),
						'id'            => 'stripe_mandate_acceptance_option',
						'wrapper_class' => 'stripe-mandate-acceptance-option',
						'type'          => 'radio_inline',
						'default'       => 'enabled',
						'options'       => [
							'enabled'  => esc_html__( 'Enabled', 'give' ),
							'disabled' => esc_html__( 'Disabled', 'give' ),
						],
					];

					$is_hide_mandate = give_is_setting_enabled( give_get_option( 'stripe_mandate_acceptance_option', 'enabled' ) );

					$settings['sepa'][] = [
						'name'          => esc_html__( 'Mandate Acceptance Text', 'give' ),
						'desc'          => esc_html__( 'This text displays below the IBAN field and should provide clarity to your donors on how this payment option works.', 'give' ),
						'id'            => 'stripe_mandate_acceptance_text',
						'wrapper_class' => $is_hide_mandate ? 'stripe-mandate-acceptance-text' : 'stripe-mandate-acceptance-text give-hidden',
						'type'          => 'textarea',
						'default'       => give_stripe_get_default_mandate_acceptance_text(),
					];

					/**
					 * This filter is used to add setting fields after sepa fields.
					 *
					 * @since 2.6.1
					 */
					$settings = apply_filters( 'give_stripe_after_sepa_fields', $settings );

					// Stripe Admin Settings - Footer.
					$settings['sepa'][] = [
						'id'   => 'give_title_stripe_sepa',
						'type' => 'sectionend',
					];

					// BECS Direct Debit.
					$settings['becs'][] = [
						'id'   => 'give_title_stripe_becs',
						'type' => 'title',
					];

					$settings['becs'][] = [
						'name'          => esc_html__( 'Display Icon', 'give' ),
						'desc'          => esc_html__( 'This option allows you to display a bank building icon within the bank account input field for BECS Direct Debit.', 'give' ),
						'id'            => 'stripe_becs_hide_icon',
						'wrapper_class' => 'stripe-becs-hide-icon',
						'type'          => 'radio_inline',
						'default'       => 'enabled',
						'options'       => [
							'enabled'  => esc_html__( 'Enabled', 'give' ),
							'disabled' => esc_html__( 'Disabled', 'give' ),
						],
					];

					$is_becs_hide_icon = give_is_setting_enabled( give_get_option( 'stripe_becs_hide_icon', 'enabled' ) );

					$settings['becs'][] = [
						'name'          => esc_html__( 'Icon Style', 'give' ),
						'desc'          => esc_html__( 'This option allows you to select the icon style for the IBAN element of SEPA Direct Debit.', 'give' ),
						'id'            => 'stripe_becs_icon_style',
						'wrapper_class' => $is_becs_hide_icon ? 'stripe-becs-icon-style' : 'stripe-becs-icon-style give-hidden',
						'type'          => 'radio_inline',
						'default'       => 'default',
						'options'       => [
							'default' => esc_html__( 'Default', 'give' ),
							'solid'   => esc_html__( 'Solid', 'give' ),
						],
					];

					$settings['becs'][] = [
						'name'          => esc_html__( 'Display Mandate Acceptance', 'give' ),
						'desc'          => esc_html__( 'The mandate acceptance text is meant to explain to your donors how the payment processing will work for their donation. The text will display below the Bank Account fields.', 'give' ),
						'id'            => 'stripe_becs_mandate_acceptance_option',
						'wrapper_class' => 'stripe-becs-mandate-acceptance-option',
						'type'          => 'radio_inline',
						'default'       => 'enabled',
						'options'       => [
							'enabled'  => esc_html__( 'Enabled', 'give' ),
							'disabled' => esc_html__( 'Disabled', 'give' ),
						],
					];

					$is_hide_mandate = give_is_setting_enabled( give_get_option( 'stripe_becs_mandate_acceptance_option', 'enabled' ) );

					$settings['becs'][] = [
						'name'          => esc_html__( 'Mandate Acceptance Text', 'give' ),
						'desc'          => esc_html__( 'This text displays below the Bank Account fields and should provide clarity to your donors on how this payment option works.', 'give' ),
						'id'            => 'stripe_becs_mandate_acceptance_text',
						'wrapper_class' => $is_hide_mandate ? 'stripe-becs-mandate-acceptance-text' : 'stripe-becs-mandate-acceptance-text give-hidden',
						'type'          => 'textarea',
						'default'       => give_stripe_get_default_mandate_acceptance_text( 'becs' ),
					];

					/**
					 * This filter is used to add setting fields after BECS Direct Debit fields.
					 *
					 * @since 2.7.0
					 */
					$settings = apply_filters( 'give_stripe_after_becs_fields', $settings );

					// Stripe Admin Settings - Footer.
					$settings['becs'][] = [
						'id'   => 'give_title_stripe_becs',
						'type' => 'sectionend',
					];

					/**
					 * This filter is used to add setting fields for additional groups.
					 *
					 * @since 2.5.5
					 */
					$settings = apply_filters( 'give_stripe_add_additional_group_fields', $settings );

					break;
			} // End switch().

			return $settings;
		}

		/**
		 * Add advanced Stripe settings.
		 *
		 * New tab under Settings > Advanced that allows users to use their own API key.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @param array $settings List of settings.
		 *
		 * @return array
		 */
		public function register_advanced_settings( $settings ) {

			$current_section = give_get_current_setting_section();

			// Bailout, if stripe is not the current section.
			if ( 'stripe' !== $current_section ) {
				return $settings;
			}

			$stripe_fonts = give_get_option( 'stripe_fonts', 'google_fonts' );

			switch ( $current_section ) {

				case 'stripe':
					$settings = [
						[
							'id'   => 'give_title_stripe_advanced',
							'type' => 'title',
						],
					];

					/**
					 * This filter hook is used to add setting fields before stripe advanced settings.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_before_advanced_setting_fields', $settings );

					$settings[] = [
						'name' => __( 'Stripe JS Incompatibility', 'give' ),
						'desc' => __( 'If your site has problems with processing cards using Stripe JS, check this option to use a fallback method of processing.', 'give' ),
						'id'   => 'stripe_js_fallback',
						'type' => 'checkbox',
					];

					$settings[] = [
						'name' => __( 'Stripe Styles', 'give' ),
						'desc' => __( 'Edit the properties above to match the look and feel of your WordPress theme. These styles will be applied to Stripe Credit Card fields including Card Number, CVC and Expiration. Any valid CSS property can be defined, however, it must be formatted as JSON, not CSS. For more information on Styling Stripe CC fields please see this <a href="https://stripe.com/docs/stripe-js/reference#element-options" target="_blank">article</a>.', 'give' ),
						'id'   => 'stripe_styles',
						'type' => 'stripe_styles_field',
						'css'  => 'width: 100%',
					];

					$settings[] = [
						'name'    => __( 'Stripe Fonts', 'give' ),
						'desc'    => __( 'Select the type of font you want to load in Stripe Credit Card fields including Card Number, CVC and Expiration. For more information on Styling Stripe CC fields please see this <a href="https://stripe.com/docs/stripe-js/reference#stripe-elements" target="_blank">article</a>.', 'give' ),
						'id'      => 'stripe_fonts',
						'type'    => 'radio_inline',
						'default' => 'google_fonts',
						'options' => [
							'google_fonts' => __( 'Google Fonts', 'give' ),
							'custom_fonts' => __( 'Custom Fonts', 'give' ),
						],
					];

					$settings[] = [
						'name'          => __( 'Google Fonts URL', 'give' ),
						'desc'          => __( 'Please enter the Google Fonts URL which is applied to your theme to have the Stripe Credit Card fields reflect the same fonts.', 'give' ),
						'id'            => 'stripe_google_fonts_url',
						'type'          => 'text',
						'wrapper_class' => 'give-stripe-google-fonts-wrap ' . ( 'google_fonts' !== $stripe_fonts ? 'give-hidden' : '' ),
					];

					$settings[] = [
						'name'          => __( 'Custom Fonts', 'give' ),
						'desc'          => __( 'Edit the font properties above to match the fonts of your WordPress theme. These font properties will be applied to Stripe Credit Card fields including Card Number, CVC and Expiration. However, it must be formatted as JSON, not CSS.', 'give' ),
						'wrapper_class' => 'give-stripe-custom-fonts-wrap ' . ( 'custom_fonts' !== $stripe_fonts ? 'give-hidden' : '' ),
						'id'            => 'stripe_custom_fonts',
						'type'          => 'textarea',
						'default'       => '{}',
					];

					/**
					 * This filter hook is used to add setting fields after stripe advanced settings.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_after_advanced_setting_fields', $settings );

					$settings[] = [
						'id'   => 'give_title_stripe_advanced',
						'type' => 'sectionend',
					];
					break;
			} // End switch().

			// Output.
			return $settings;

		}

		/**
		 * This function return hidden for fields which should get hidden on toggle of modal checkout checkbox.
		 *
		 * @param string $status Status - Enabled or Disabled.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return string
		 */
		public function stripe_modal_checkout_status( $status = 'enabled' ) {

			$checkout_type = give_stripe_get_checkout_type();

			if ( 'redirect' === $checkout_type ) {
				return 'give-hidden';
			}

			return '';
		}

		/**
		 * Stripe Account Manager Field.
		 *
		 * This function will add a custom admin settings field to display
		 * Stripe Account Management field where all the connected Stripe
		 * accounts will display irrespective of the Stripe account connected
		 * via Connect method or Manual method.
		 *
		 * @param array  $field        Field Arguments.
		 * @param string $option_value Field Value.
		 *
		 * @return mixed|void
		 * @since 2.7.0
		 * @since 2.9.0 Stripe accounts are now disconnected locally, meaning that GiveWP remains an Authorized Application in the Stripe account.
		 */
		public function stripe_account_manager_field( $field, $option_value ) {
			$stripe_accounts = give_stripe_get_all_accounts();
			$default_account = give_stripe_get_default_account_slug();

			// Set account as default.
			if ( 1 === count( $stripe_accounts ) ) {
				$stripe_account_keys = array_keys( $stripe_accounts );
				$default_account     = $stripe_account_keys[0];
			}

			$site_url            = get_site_url();
			$modal_title         = sprintf(
				'<strong>%1$s</strong>',
				esc_html__( 'You are connected! Now this is important: Please configure your Stripe webhook to finalize the setup.', 'give' )
			);
			$modal_first_detail  = sprintf(
				'%1$s %2$s',
				esc_html__( 'In order for Stripe to function properly, you must add a new Stripe webhook endpoint. To do this please visit the <a href=\'https://dashboard.stripe.com/webhooks\' target=\'_blank\'>Webhooks Section of your Stripe Dashboard</a> and click the <strong>Add endpoint</strong> button and paste the following URL:', 'give' ),
				"<strong>{$site_url}?give-listener=stripe</strong>"
			);
			$modal_second_detail = esc_html__( 'Stripe webhooks are required so GiveWP can communicate properly with the payment gateway to confirm payment completion, renewals, and more.', 'give' );
			$can_display         = ! empty( $_GET['stripe_account'] ) ? '0' : '1';
			?>
			<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . esc_attr( $field['wrapper_class'] ) . '"' : ''; ?>>
				<td class="give-forminp give-forminp-api_key">
					<div id="give-stripe-account-manager-errors"></div>
					<div id="give-stripe-account-manager-description">
						<h2><?php esc_html_e( 'Manage Your Stripe Accounts', 'give' ); ?></h2>
						<p class="give-field-description"><?php esc_html_e( 'Connect to the Stripe payment gateway using this section. Multiple Stripe accounts can be connected simultaneously. All donation forms will use the "Default Account" unless configured otherwise. To specify a different Stripe account for a form, configure the settings within the "Stripe Account" tab on the individual form edit screen.', 'give' ); ?></p>
						<?php
						if ( ! give_stripe_is_premium_active() ) {
							?>
							<p class="give-field-description">
								<br />
								<?php
								echo sprintf(
									__( 'NOTE: You are using the free Stripe payment gateway integration. This includes an additional 2%% fee for processing one-time donations. This fee is removed by activating the premium <a href="%1$s" target="_blank">Stripe add-on</a> and never applies to subscription donations made through the <a href="%2$s" target="_blank">Recurring Donations add-on</a>. <a href="%3$s" target="_blank">Learn More ></a>', 'give' ),
									esc_url( 'http://docs.givewp.com/settings-stripe-addon' ),
									esc_url( 'http://docs.givewp.com/settings-stripe-recurring' ),
									esc_url( 'http://docs.givewp.com/settings-stripe-free' )
								);
								?>
							</p>
							<?php
						}
						?>
					</div>
					<div class="give-stripe-account-manager-container">
						<div
							id="give-stripe-connected"
							class="stripe-btn-disabled give-hidden"
							data-status="connected"
							data-title="<?php echo $modal_title; ?>"
							data-first-detail="<?php echo $modal_first_detail; ?>"
							data-second-detail="<?php echo $modal_second_detail; ?>"
							data-display="<?php echo $can_display; ?>"
							data-redirect-url="<?php echo esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ); ?>"
						>
						</div>
						<div class="give-stripe-account-manager-list">
							<?php
							if ( $stripe_accounts ) {
								foreach ( $stripe_accounts as $slug => $details ) {
									$account_name       = $details['account_name'];
									$account_email      = $details['account_email'];
									$stripe_account_id  = $details['account_id'];
									$disconnect_message = esc_html__( 'Are you sure you want to disconnect this Stripe account?', 'give' );
									$disconnect_url     = add_query_arg(
										[
											'post_type'   => 'give_forms',
											'page'        => 'give-settings',
											'tab'         => 'gateways',
											'section'     => 'stripe-settings',
											'give_action' => ( 'connect' === $details['type'] )
												? 'disconnect_connected_stripe_account'
												: 'disconnect_manual_stripe_account',
											'give_stripe_disconnect_slug' => $slug,
										],
										wp_nonce_url( admin_url( 'edit.php' ), 'give_disconnect_connected_stripe_account_' . $slug )
									);
									?>
									<div id="give-stripe-<?php echo $slug; ?>" class="give-stripe-account-manager-list-item">
										<div class="give-stripe-account-name-wrap">
											<span class="give-stripe-account-name">
												<?php echo esc_html( $account_name ); ?>
											</span>
											<span class="give-field-description give-stripe-account-email">
												<?php echo esc_html( $account_email ); ?>
											</span>
											<span class="give-stripe-account-edit">
												<?php if ( 'connect' !== $details['type'] ) { ?>
												<a class="give-stripe-account-edit-name" href="#"><?php esc_html_e( 'Edit', 'give' ); ?></a>
												<a
													class="give-stripe-account-update-name give-hidden"
													href="#"
													data-account="<?php echo $slug; ?>"
												><?php esc_html_e( 'Update', 'give' ); ?></a>
												<a class="give-stripe-account-cancel-name give-hidden" href="#"><?php esc_html_e( 'Cancel', 'give' ); ?></a>
												<?php } ?>
											<?php if ( $slug === $default_account ) { ?>
												<span class="give-stripe-account-default give-stripe-account-badge">
													<?php esc_html_e( 'Default Account', 'give' ); ?>
												</span>
											<?php } else { ?>
												<span class="give-stripe-account-default">
													<a
														data-account="<?php echo $slug; ?>"
														data-url="<?php echo give_stripe_get_admin_settings_page_url(); ?>"
														class="give-stripe-account-set-default" href="#"
													><?php esc_html_e( 'Set as Default', 'give' ); ?></a>
												</span>
											<?php } ?>

											</span>
										</div>
										<div class="give-stripe-account-actions">
											<span class="give-stripe-account-type-description give-field-description"><?php esc_html_e( 'Connection Method:', 'give' ); ?></span>
											<span class="give-stripe-account-type-method"><?php echo give_stripe_connection_type_name( $details['type'] ); ?></span>
											<?php
											if (
												$slug !== $default_account ||
												count( $stripe_accounts ) === 1
											) {
												?>
												<span class="give-stripe-account-disconnect">
													<a
														class="give-stripe-disconnect-account-btn"
														href="<?php echo $disconnect_url; ?>"
														data-disconnect-message="<?php echo $disconnect_message; ?>"
														data-account="<?php echo $slug; ?>"
													>
														<?php esc_html_e( 'Disconnect', 'give' ); ?>
													</a>
												</span>
											<?php } ?>
										</div>
									</div>
									<?php
								}
							} else {
								?>
								<div class="give-stripe-account-manager-list-item">
									<span><?php esc_html_e( 'No Stripe Accounts Connected.', 'give' ); ?></span>
								</div>
							<?php } ?>
						</div>
						<div class="give-stripe-account-manager-add-section">
							<?php
							// Show option to add Stripe when the manual upgrade is completed.
							if ( give_has_upgrade_completed( 'v270_store_stripe_account_for_donation' ) ) {
								?>
								<h3><?php esc_html_e( 'Connect a New Stripe Account', 'give' ); ?></h3>
								<div class="give-stripe-add-account-errors"></div>
								<table class="form-table give-setting-tab-body give-setting-tab-body-gateways">
									<tbody>
									<?php
									if ( give_stripe_is_premium_active() ) {
										/**
										 * This action hook will be used to load Manual API fields for premium addon.
										 *
										 * @param array $stripe_accounts All Stripe accounts.
										 *
										 * @since 2.7.0
										 */
										do_action( 'give_stripe_premium_manual_api_fields', $stripe_accounts );
									}
									?>
									<tr valign="top" class="give-stripe-account-type-connect">
										<th scope="row" class="titledesc">
											<label for="stripe_connect_button">
												<?php esc_html_e( 'Stripe Connection', 'give' ); ?>
											</label>
										</th>
										<td class="give-forminp">
											<?php echo give_stripe_connect_button(); ?>
										</td>
									</tr>
									</tbody>
								</table>
								<?php
							} else {
								Give()->notices->print_admin_notices(
									[
										'description' => sprintf(
											'%1$s <a href="%2$s">%3$s</a> %4$s',
											esc_html__( 'Give 2.7.0 introduces the ability to connect a single site to multiple Stripe accounts. To use this feature, you need to complete database updates. ', 'give' ),
											esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-updates' ) ),
											esc_html__( 'Click here', 'give' ),
											esc_html__( 'to complete your pending database updates.', 'give' )
										),
										'dismissible' => false,
									]
								);
							}
							?>
						</div>
					</div>
					<!-- DESTRUCTIVE: Disconnect all Stripe accounts. -->
					<hr class="give-stripe-disconnect-all-divider" />
					<div class="give-stripe-disconnect-all">
						<h3><?php esc_html_e( 'Remove GiveWP as an Authorized Application for my Stripe account.', 'give' ); ?></h3>
						<p class="give-stripe-disconnect-all--description">
							<?php esc_html_e( 'Removing GiveWP as an Authorized Application for your Stripe account will remove the connection between the website you are disconnecting as well as any other sites that you have connected to GiveWP using the same Stripe account and connect method. Proceed with caution when disconnecting if you have multiple sites connected.', 'give' ); ?>
						</p>
						<p>
							<a target="_blank" href="https://dashboard.stripe.com/account/applications">
								<?php esc_html_e( 'View authorized applications in Stripe', 'give' ); ?>
							</a>
						</p>
					</div>
				</td>
			</tr>
			<?php
		}

		/**
		 * Stripe Webhook field.
		 *
		 * @since 2.5.0
		 *
		 * @param $value
		 * @param $option_value
		 */
		public function stripe_webhook_field( $value, $option_value ) {
			?>
			<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : ''; ?>>
				<th scope="row" class="titledesc">
					<label for=""><?php _e( 'Stripe Webhooks', 'give' ); ?></label>
				</th>

				<td class="give-forminp give-forminp-api_key">
					<div class="give-stripe-webhook-sync-wrap">
						<p class="give-stripe-webhook-explanation" style="margin-bottom: 15px;">
							<?php
							esc_html_e( 'In order for Stripe to function properly, you must configure your Stripe webhooks.', 'give' );
							echo sprintf(
								/* translators: 1. Webhook settings page. */
								__( ' You can  visit your <a href="%1$s" target="_blank">Stripe Account Dashboard</a> to add a new webhook. ', 'give' ),
								esc_url_raw( 'https://dashboard.stripe.com/account/webhooks' )
							);
							esc_html_e( 'Please add a new webhook endpoint for the following URL:', 'give' );
							?>
						</p>
						<p style="margin-bottom: 15px;">
							<strong><?php echo esc_html__( 'Webhook URL:', 'give' ); ?></strong>
							<input style="width: 400px;" type="text" readonly="true"
								   value="<?php echo site_url() . '/?give-listener=stripe'; ?>"/>
						</p>
						<?php
						$webhook_received_on = give_get_option( 'give_stripe_last_webhook_received_timestamp' );
						if ( ! empty( $webhook_received_on ) ) {
							$date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
							?>
							<p>
								<strong><?php esc_html_e( 'Last webhook received on', 'give' ); ?></strong> <?php echo date_i18n( esc_html( $date_time_format ), $webhook_received_on ); ?>
							</p>
							<?php
						}
						?>
						<p>
							<?php
							echo sprintf(
								/* translators: 1. Documentation on webhook setup. */
								__( 'See our <a href="%1$s" target="_blank">documentation</a> for more information.', 'give' ),
								esc_url_raw( 'http://docs.givewp.com/stripe-webhooks' )
							);
							?>
						</p>
					</div>

					<p class="give-field-description">
						<?php esc_html_e( 'Stripe webhooks are critical to configure so that GiveWP can recieve communication properly from the payment gateway. Webhooks for test-mode donations are configured separately on the Stripe dashboard. Note: webhooks do not function on localhost or websites in maintenance mode.', 'give' ); ?>
					</p>
				</td>
			</tr>
			<?php
		}

		/**
		 * Advanced Stripe Styles field to manage theme stylings for Stripe CC fields.
		 *
		 * @param array  $field_options List of field options.
		 * @param string $option_value  Option value.
		 *
		 * @since  2.5.0
		 * @access public
		 */
		public function stripe_styles_field( $field_options, $option_value ) {

			$default_attributes  = [
				'rows' => 10,
				'cols' => 60,
			];
			$textarea_attributes = isset( $value['attributes'] ) ? $field_options['attributes'] : [];

			// Make sure empty textarea have default valid json data so that the textarea doesn't show error.
			$base_styles_value     = ! empty( $option_value['base'] ) ? trim( $option_value['base'] ) : give_stripe_get_default_base_styles();
			$empty_styles_value    = ! empty( $option_value['empty'] ) ? trim( $option_value['empty'] ) : '{}';
			$invalid_styles_value  = ! empty( $option_value['invalid'] ) ? trim( $option_value['invalid'] ) : '{}';
			$complete_styles_value = ! empty( $option_value['complete'] ) ? trim( $option_value['complete'] ) : '{}';

			?>
			<tr valign="top" <?php echo ! empty( $field_options['wrapper_class'] ) ? 'class="' . esc_attr( $field_options['wrapper_class'] ) . '"' : ''; ?>>
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_html( $field_options['type'] ); ?>">
						<?php echo esc_attr( $field_options['title'] ); ?>
					</label>
				</th>
				<td class="give-forminp give-forminp-<?php echo esc_html( $field_options['type'] ); ?>">
					<div>
						<p>
							<strong><?php esc_attr_e( 'Base Styles', 'give' ); ?></strong>
						</p>
						<p>
							<textarea
								name="stripe_styles[base]"
								id="<?php echo esc_attr( $field_options['id'] ) . '_base'; ?>"
								style="<?php echo esc_attr( $field_options['css'] ); ?>"
								class="<?php echo esc_attr( $field_options['class'] ); ?>"
								<?php echo give_get_attribute_str( $textarea_attributes, $default_attributes ); ?>
							><?php echo esc_textarea( $base_styles_value ); ?></textarea>
						</p>
					</div>
					<div>
						<p>
							<strong><?php esc_attr_e( 'Empty Styles', 'give' ); ?></strong>
						</p>
						<p>
							<textarea
								name="stripe_styles[empty]"
								id="<?php echo esc_attr( $field_options['id'] ) . '_empty'; ?>"
								style="<?php echo esc_attr( $field_options['css'] ); ?>"
								class="<?php echo esc_attr( $field_options['class'] ); ?>"
								<?php echo give_get_attribute_str( $textarea_attributes, $default_attributes ); ?>
							>
								<?php echo esc_textarea( $empty_styles_value ); ?>
							</textarea>
						</p>
					</div>
					<div>
						<p>
							<strong><?php esc_attr_e( 'Invalid Styles', 'give' ); ?></strong>
						</p>
						<p>
							<textarea
								name="stripe_styles[invalid]"
								id="<?php echo esc_attr( $field_options['id'] ) . '_invalid'; ?>"
								style="<?php echo esc_attr( $field_options['css'] ); ?>"
								class="<?php echo esc_attr( $field_options['class'] ); ?>"
								<?php echo give_get_attribute_str( $textarea_attributes, $default_attributes ); ?>
							>
								<?php echo esc_textarea( $invalid_styles_value ); ?>
							</textarea>
						</p>
					</div>
					<div>
						<p>
							<strong><?php esc_attr_e( 'Complete Styles', 'give' ); ?></strong>
						</p>
						<p>
							<textarea
								name="stripe_styles[complete]"
								id="<?php echo esc_attr( $field_options['id'] ) . '_complete'; ?>"
								style="<?php echo esc_attr( $field_options['css'] ); ?>"
								class="<?php echo esc_attr( $field_options['class'] ); ?>"
								<?php echo give_get_attribute_str( $textarea_attributes, $default_attributes ); ?>
							>
								<?php echo esc_textarea( $complete_styles_value ); ?>
							</textarea>
						</p>
					</div>
					<p class="give-field-description">
						<?php echo $field_options['desc']; ?>
					</p>
				</td>
			</tr>
			<?php
		}

		/**
		 * @since 2.8.0
		 */
		public function disconnect_connected_stripe_account() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$data = give_clean( $_GET );
			if ( ! isset( $data['give_stripe_disconnect_slug'] ) ) {
				return;
			}
			check_admin_referer( 'give_disconnect_connected_stripe_account_' . $data['give_stripe_disconnect_slug'] );
			give_stripe_disconnect_account(
				$data['give_stripe_disconnect_slug']
			);
		}
	}
}

new Give_Stripe_Admin_Settings();
