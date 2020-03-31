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

			add_filter( 'give_get_sections_gateways', array( $this, 'register_sections' ) );
			add_filter( 'give_get_groups_stripe-settings', array( $this, 'register_groups' ) );
			add_filter( 'give_get_settings_gateways', array( $this, 'register_settings' ) );
			add_filter( 'give_get_sections_advanced', array( $this, 'register_advanced_sections' ) );
			add_filter( 'give_get_settings_advanced', array( $this, 'register_advanced_settings' ), 10, 1 );
			add_action( 'give_admin_field_stripe_connect', array( $this, 'stripe_connect_field' ), 10, 2 );
			add_action( 'give_admin_field_stripe_webhooks', array( $this, 'stripe_webhook_field' ), 10, 2 );
			add_action( 'give_admin_field_stripe_styles_field', array( $this, 'stripe_styles_field' ), 10, 2 );
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

			$groups = array(
				'general'     => __( 'General Settings', 'give' ),
				'credit-card' => __( 'Credit Card On Site', 'give' ),
				'checkout'    => __( 'Stripe Checkout', 'give' ),
				'sepa'        => __( 'SEPA Direct Debit', 'give' ),
			);

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
					// Stripe Admin Settings - Header.
					$settings['general'][] = array(
						'id'   => 'give_title_stripe_general',
						'type' => 'title',
					);

					if ( apply_filters( 'give_stripe_show_connect_button', true ) ) {
						// Stripe Admin Settings - Configuration Fields.
						$settings['general'][] = array(
							'name'          => __( 'Stripe Connect', 'give' ),
							'desc'          => '',
							'wrapper_class' => 'give-stripe-connect-tr',
							'id'            => 'stripe_connect',
							'type'          => 'stripe_connect',
						);
					}

					/**
					 * This filter hook is used to add configuration fields like api key, access token, oAuth button, etc.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_configuration_fields', $settings );

					$settings['general'][] = array(
						'name'          => __( 'Stripe Webhooks', 'give' ),
						'desc'          => '',
						'wrapper_class' => 'give-stripe-webhooks-tr',
						'id'            => 'stripe_webhooks',
						'type'          => 'stripe_webhooks',
					);

					$settings['general'][] = array(
						'name'       => __( 'Statement Descriptor', 'give' ),
						'desc'       => __( 'This is the text that appears on your donor\'s bank statements. Statement descriptors are limited to 22 characters, cannot use the special characters <code><</code>, <code>></code>, <code>\'</code>, or <code>"</code>, and must not consist solely of numbers. This is typically the name of your website or organization.', 'give' ),
						'id'         => 'stripe_statement_descriptor',
						'type'       => 'text',
						'attributes' => array(
							'maxlength'   => '22',
							'placeholder' => get_bloginfo( 'name' ),
						),
						'default'    => get_bloginfo( 'name' ),
					);

					$settings['general'][] = array(
						'name' => __( 'Collect Billing Details', 'give' ),
						'desc' => __( 'This option will enable the billing details section for Stripe which requires the donor\'s address to complete the donation. These fields are not required by Stripe to process the transaction, but you may have the need to collect the data.', 'give' ),
						'id'   => 'stripe_collect_billing',
						'type' => 'checkbox',
					);

					/**
					 * This filter hook is used to add fields after Stripe General fields.
					 *
					 * @since 2.5.5
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_after_general_fields', $settings );

					$settings['general'][] = array(
						'name' => __( 'Stripe Receipt Emails', 'give' ),
						'desc' => sprintf(
							/* translators: 1. GiveWP Support URL */
							__( 'Check this option if you would like donors to receive receipt emails directly from Stripe. By default, donors will receive GiveWP generated <a href="%1$s" target="_blank">receipt emails</a>.', 'give' ),
							admin_url( '/edit.php?post_type=give_forms&page=give-settings&tab=emails' )
						),
						'id'   => 'stripe_receipt_emails',
						'type' => 'checkbox',
					);

					$settings['general'][] = array(
						'name'  => __( 'Stripe Gateway Documentation', 'give' ),
						'id'    => 'display_settings_general_docs_link',
						'url'   => esc_url( 'http://docs.givewp.com/addon-stripe' ),
						'title' => __( 'Stripe Gateway Documentation', 'give' ),
						'type'  => 'give_docs_link',
					);

					// Stripe Admin Settings - Footer.
					$settings['general'][] = array(
						'id'   => 'give_title_stripe_general',
						'type' => 'sectionend',
					);

					// Stripe Admin Settings - Header.
					$settings['credit-card'][] = array(
						'id'   => 'give_title_stripe_credit_card',
						'type' => 'title',
					);

					/**
					 * This filter hook is used to add fields before Stripe Credit Card fields.
					 *
					 * @since 2.5.5
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_before_credit_card_fields', $settings );

					$settings['credit-card'][] = array(
						'name'          => __( 'Credit Card Fields Format', 'give' ),
						'desc'          => __( 'This option allows you to show single or multiple credit card fields on your donation forms.', 'give' ),
						'id'            => 'stripe_cc_fields_format',
						'wrapper_class' => 'stripe-cc-field-format-settings',
						'type'          => 'radio_inline',
						'default'       => 'multi',
						'options'       => array(
							'single' => __( 'Single Field', 'give' ),
							'multi'  => __( 'Multi Field', 'give' ),
						),
					);

					/**
					 * This filter hook is used to add fields after Stripe Credit Card fields.
					 *
					 * @since 2.5.5
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_after_credit_card_fields', $settings );

					$settings['credit-card'][] = array(
						'name'  => __( 'Stripe Gateway Documentation', 'give' ),
						'id'    => 'display_settings_credit_card_docs_link',
						'url'   => esc_url( 'http://docs.givewp.com/addon-stripe' ),
						'title' => __( 'Stripe Gateway Documentation', 'give' ),
						'type'  => 'give_docs_link',
					);

					// Stripe Admin Settings - Footer.
					$settings['credit-card'][] = array(
						'id'   => 'give_title_stripe_credit_card',
						'type' => 'sectionend',
					);

					/**
					 * This filter hook is used to add fields before Stripe Checkout fields.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_before_checkout_fields', $settings );

					// Checkout.
					$settings['checkout'][] = array(
						'id'   => 'give_title_stripe_checkout',
						'type' => 'title',
					);

					$settings['checkout'][] = array(
						'name'          => __( 'Checkout Type', 'give' ),
						'desc'          => sprintf( __( 'This option allows you to select from the two types of Stripe Checkout methods available for processing donations. The "Modal" option is the <a href="%1$s" target="_blank">legacy Stripe Checkout</a> and is not SCA compatible. The "Redirect" option uses Stripe\'s new <a href="%2$s" target="_blank">Checkout</a> interface and offers donors an easy way to pay with Credit Card, Apple, and Google Pay. As well, it is SCA compatible and fully supported by Stripe and GiveWP.', 'give' ), 'https://stripe.com/docs/legacy-checkout', 'https://stripe.com/docs/payments/checkout' ),
						'id'            => 'stripe_checkout_type',
						'wrapper_class' => 'stripe-checkout-type',
						'type'          => 'radio_inline',
						'default'       => 'modal',
						'options'       => array(
							'modal'    => __( 'Modal (Legacy Checkout)', 'give' ),
							'redirect' => __( 'Redirect (Checkout 2.0)', 'give' ),
						),
					);

					$settings['checkout'][] = array(
						'name'          => __( 'Checkout Heading', 'give' ),
						'desc'          => __( 'This is the main heading within the modal checkout. Typically, this is the name of your organization, cause, or website.', 'give' ),
						'id'            => 'stripe_checkout_name',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => get_bloginfo( 'name' ),
						'type'          => 'text',
					);

					$settings['checkout'][] = array(
						'name'          => __( 'Stripe Checkout Image', 'give' ),
						'desc'          => __( 'This image appears in when the Stripe checkout modal window opens and provides better brand recognition that leads to increased conversion rates. The recommended minimum size is a square image at 128x128px. The supported image types are: .gif, .jpeg, and .png.', 'give' ),
						'id'            => 'stripe_checkout_image',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'type'          => 'file',
						// Optional.
						'options'       => array(
							'url' => false, // Hide the text input for the url.
						),
						'text'          => array(
							'add_upload_file_text' => __( 'Add or Upload Image', 'give' ),
						),
					);

					$settings['checkout'][] = array(
						'name'    => __( 'Processing Text', 'give' ),
						'desc'    => __( 'This text appears briefly once the donor has submitted a donation while GiveWP is confirming the payment with the Stripe API.', 'give' ),
						'id'      => 'stripe_checkout_processing_text',
						'default' => __( 'Donation Processing...', 'give' ),
						'type'    => 'text',
					);

					$settings['checkout'][] = array(
						'name'          => __( 'Verify Zip Code', 'give' ),
						'desc'          => __( 'Specify whether Checkout should validate the billing ZIP code of the donor for added fraud protection.', 'give' ),
						'id'            => 'stripe_checkout_zip_verify',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => 'on',
						'type'          => 'checkbox',
					);

					$settings['checkout'][] = array(
						'name'          => __( 'Remember Me', 'give' ),
						'desc'          => __( 'Specify whether to include the option to "Remember Me" for future donations.', 'give' ),
						'id'            => 'stripe_checkout_remember_me',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => 'on',
						'type'          => 'checkbox',
					);

					/**
					 * This filter hook is used to add fields after Stripe Checkout fields.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_after_checkout_fields', $settings );

					$settings['checkout'][] = array(
						'name'  => __( 'Stripe Gateway Documentation', 'give' ),
						'id'    => 'display_settings_checkout_docs_link',
						'url'   => esc_url( 'http://docs.givewp.com/addon-stripe' ),
						'title' => __( 'Stripe Gateway Documentation', 'give' ),
						'type'  => 'give_docs_link',
					);

					// Stripe Admin Settings - Footer.
					$settings['checkout'][] = array(
						'id'   => 'give_title_stripe_checkout',
						'type' => 'sectionend',
					);

					// SEPA Direct Debit.
					$settings['sepa'][] = array(
						'id'   => 'give_title_stripe_sepa',
						'type' => 'title',
					);

					$settings['sepa'][] = array(
						'name'          => __( 'Display Icon', 'give' ),
						'desc'          => __( 'This option allows you to display a bank building icon within the IBAN input field for SEPA Direct Debit.', 'give' ),
						'id'            => 'stripe_hide_icon',
						'wrapper_class' => 'stripe-hide-icon',
						'type'          => 'radio_inline',
						'default'       => 'enabled',
						'options'       => array(
							'enabled'  => __( 'Enabled', 'give' ),
							'disabled' => __( 'Disabled', 'give' ),
						),
					);

					$is_hide_icon = give_is_setting_enabled( give_get_option( 'stripe_hide_icon' ) );

					$settings['sepa'][] = array(
						'name'          => __( 'Icon Style', 'give' ),
						'desc'          => __( 'This option allows you to select the icon style for the IBAN element of SEPA Direct Debit.', 'give' ),
						'id'            => 'stripe_icon_style',
						'wrapper_class' => $is_hide_icon ? 'stripe-icon-style' : 'stripe-icon-style give-hidden',
						'type'          => 'radio_inline',
						'default'       => 'default',
						'options'       => array(
							'default' => __( 'Default', 'give' ),
							'solid'   => __( 'Solid', 'give' ),
						),
					);


					$settings['sepa'][] = array(
						'name'          => __( 'Display Mandate Acceptance', 'give' ),
						'desc'          => __( 'The mandate acceptance text is meant to explain to your donors how the payment processing will work for their donation. The text will display below the IBAN field.', 'give' ),
						'id'            => 'stripe_mandate_acceptance_option',
						'wrapper_class' => 'stripe-mandate-acceptance-option',
						'type'          => 'radio_inline',
						'default'       => 'enabled',
						'options'       => array(
							'enabled'  => __( 'Enabled', 'give' ),
							'disabled' => __( 'Disabled', 'give' ),
						),
					);

					$is_hide_mandate = give_is_setting_enabled( give_get_option( 'stripe_mandate_acceptance_option' ) );

					$settings['sepa'][] = array(
						'name'          => __( 'Mandate Acceptance Text', 'give' ),
						'desc'          => __( 'This text displays below the IBAN field and should provide clarity to your donors on how this payment option works.', 'give' ),
						'id'            => 'stripe_mandate_acceptance_text',
						'wrapper_class' => $is_hide_mandate ? 'stripe-mandate-acceptance-text' : 'stripe-mandate-acceptance-text give-hidden',

						'type'          => 'textarea',
						'default'       => give_stripe_get_default_mandate_acceptance_text(),
					);

					/**
					 * This filter is used to add setting fields after sepa fields.
					 *
					 * @since 2.6.1
					 */
					$settings = apply_filters( 'give_stripe_after_sepa_fields', $settings );

					// Stripe Admin Settings - Footer.
					$settings['sepa'][] = array(
						'id'   => 'give_title_stripe_sepa',
						'type' => 'sectionend',
					);

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
					$settings = array(
						array(
							'id'   => 'give_title_stripe_advanced',
							'type' => 'title',
						),
					);

					/**
					 * This filter hook is used to add setting fields before stripe advanced settings.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_before_advanced_setting_fields', $settings );

					$settings[] = array(
						'name'    => __( 'Stripe SDK Compatibility', 'give' ),
						'desc'    => sprintf(
							/* translators: 1. GiveWP Support URL */
							__( 'If you are using another plugin that uses Stripe to accept payments there is a chance that it may include the <a href="%1$s" target="_blank">Stripe SDK</a> (Software Development Kit) either through <a href="%2$s" target="_blank">Composer</a> or manually initalized. This can cause conflicts with GiveWP because WordPress does not have a dependency management system to prevent conflicts. To help resolve conflicts we have included two options to use Stripe alongside these other plugins. The recommended way is Composer, but if that is not working then we recommend manual initialization. If both options do not work please <a href="%3$s" target="_blank">contact support</a>.', 'give' ),
							esc_url_raw( 'https://github.com/stripe/stripe-php' ),
							esc_url_raw( 'http://getcomposer.org/' ),
							esc_url_raw( 'https://givewp.com/support' )
						),
						'id'      => 'stripe_sdk_incompatibility',
						'type'    => 'radio_inline',
						'options' => array(
							'composer' => __( 'Composer Autoloading', 'give' ),
							'manual'   => __( 'Manual Initialization', 'give' ),
						),
						'default' => 'composer',
					);

					$settings[] = array(
						'name' => __( 'Stripe JS Incompatibility', 'give' ),
						'desc' => __( 'If your site has problems with processing cards using Stripe JS, check this option to use a fallback method of processing.', 'give' ),
						'id'   => 'stripe_js_fallback',
						'type' => 'checkbox',
					);

					$settings[] = array(
						'name' => __( 'Stripe Styles', 'give' ),
						'desc' => __( 'Edit the properties above to match the look and feel of your WordPress theme. These styles will be applied to Stripe Credit Card fields including Card Number, CVC and Expiration. Any valid CSS property can be defined, however, it must be formatted as JSON, not CSS. For more information on Styling Stripe CC fields please see this <a href="https://stripe.com/docs/stripe-js/reference#element-options" target="_blank">article</a>.', 'give' ),
						'id'   => 'stripe_styles',
						'type' => 'stripe_styles_field',
						'css'  => 'width: 100%',
					);

					$settings[] = array(
						'name'    => __( 'Stripe Fonts', 'give' ),
						'desc'    => __( 'Select the type of font you want to load in Stripe Credit Card fields including Card Number, CVC and Expiration. For more information on Styling Stripe CC fields please see this <a href="https://stripe.com/docs/stripe-js/reference#stripe-elements" target="_blank">article</a>.', 'give' ),
						'id'      => 'stripe_fonts',
						'type'    => 'radio_inline',
						'default' => 'google_fonts',
						'options' => array(
							'google_fonts' => __( 'Google Fonts', 'give' ),
							'custom_fonts' => __( 'Custom Fonts', 'give' ),
						),
					);

					$settings[] = array(
						'name'          => __( 'Google Fonts URL', 'give' ),
						'desc'          => __( 'Please enter the Google Fonts URL which is applied to your theme to have the Stripe Credit Card fields reflect the same fonts.', 'give' ),
						'id'            => 'stripe_google_fonts_url',
						'type'          => 'text',
						'wrapper_class' => 'give-stripe-google-fonts-wrap ' . ( 'google_fonts' !== $stripe_fonts ? 'give-hidden' : '' ),
					);

					$settings[] = array(
						'name'          => __( 'Custom Fonts', 'give' ),
						'desc'          => __( 'Edit the font properties above to match the fonts of your WordPress theme. These font properties will be applied to Stripe Credit Card fields including Card Number, CVC and Expiration. However, it must be formatted as JSON, not CSS.', 'give' ),
						'wrapper_class' => 'give-stripe-custom-fonts-wrap ' . ( 'custom_fonts' !== $stripe_fonts ? 'give-hidden' : '' ),
						'id'            => 'stripe_custom_fonts',
						'type'          => 'textarea',
						'default'       => '{}',
					);

					/**
					 * This filter hook is used to add setting fields after stripe advanced settings.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_after_advanced_setting_fields', $settings );

					$settings[] = array(
						'id'   => 'give_title_stripe_advanced',
						'type' => 'sectionend',
					);
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
		 * Connect button to connect with Stripe account.
		 *
		 * @param string $value        Actual value.
		 * @param string $option_value Option value.
		 *
		 * @since  2.5.0
		 * @access public
		 */
		public function stripe_connect_field( $value, $option_value ) {
			?>
			<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . esc_attr( $value['wrapper_class'] ) . '"' : ''; ?>>
				<th scope="row" class="titledesc">
					<label for="give-stripe-connect"> <?php esc_attr_e( 'Stripe Connection', 'give' ); ?></label>
				</th>
				<td class="give-forminp give-forminp-api_key">
					<?php
					if ( give_stripe_is_connected() ) :
						$site_url            = get_site_url();
						$stripe_user_id      = give_get_option( 'give_stripe_user_id' );
						$modal_title         = __( '<strong>You are connected! Now this is important: Please now configure your Stripe webhook to finalize the setup.</strong>', 'give' );
						$modal_first_detail  = sprintf(
							'%1$s %2$s',
							__( 'In order for Stripe to function properly, you must add a new Stripe webhook endpoint. To do this please visit the <a href=\'https://dashboard.stripe.com/webhooks\' target=\'_blank\'>Webhooks Section of your Stripe Dashboard</a> and click the <strong>Add endpoint</strong> button and paste the following URL:', 'give' ),
							"<strong>{$site_url}?give-listener=stripe</strong>"
						);
						$modal_second_detail = __( 'Stripe webhooks are required so GiveWP can communicate properly with the payment gateway to confirm payment completion, renewals, and more.', 'give' );
						$can_display = ! empty( $_GET['stripe_access_token'] ) ? '0' : '1';
						?>
						<span
							id="give-stripe-connect"
							class="stripe-btn-disabled"
							data-status="connected"
							data-title="<?php echo $modal_title; ?>"
							data-first-detail="<?php echo $modal_first_detail; ?>"
							data-second-detail="<?php echo $modal_second_detail; ?>"
							data-display="<?php echo $can_display; ?>"
							data-redirect-url="<?php echo esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ); ?>"
						>
							<span><?php echo __( 'Connected', 'give' ); ?></span>
						</span>
						<p class="give-field-description">
							<span class="dashicons dashicons-yes" style="color:#25802d;"></span>
							<?php
							esc_attr_e( 'Stripe is connected.', 'give' );
							$disconnect_confirmation_message = sprintf(
								/* translators: %s Stripe User ID */
								__( 'Are you sure you want to disconnect GiveWP from Stripe? If disconnected, this website and any others sharing the same Stripe account (%s) that are connected to GiveWP will need to reconnect in order to process payments.', 'give' ),
								$stripe_user_id
							);
							?>
							<a href="<?php give_stripe_disconnect_url(); ?>" class="give-stripe-disconnect">
								<?php esc_attr_e( '[Disconnect]', 'give' ); ?>
							</a>
						</p>
					<?php else : ?>
						<?php echo give_stripe_connect_button(); ?>
						<p class="give-field-description">
							<span class="dashicons dashicons-no"
								style="color:red;"></span><?php esc_html_e( 'Stripe is NOT connected.', 'give' ); ?>
						</p>
						<?php if ( isset( $_GET['error_code'] ) && isset( $_GET['error_message'] ) ) : ?>
							<p class="stripe-connect-error">
								<strong><?php echo give_clean( $_GET['error_code'] ); ?>:</strong> <?php echo give_clean( $_GET['error_message'] ); ?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<?php
					if ( ! defined( 'GIVE_STRIPE_VERSION' ) ) {
						?>
						<p class="give-field-description">
							<?php
							echo sprintf(
								__( 'The free Stripe payment gateway includes an additional 2%% fee for processing one-time donations. This fee is removed by activating the premium <a href="%1$s" target="_blank">Stripe add-on</a> and never applies to subscription donations made through the <a href="%2$s" target="_blank">Recurring Donations add-on</a>. <a href="%3$s" target="_blank">Learn More ></a>', 'give' ),
								esc_url( 'https://givewp.com/addons/stripe-gateway/' ),
								esc_url( 'https://givewp.com/addons/recurring-donations/' ),
								esc_url( 'http://docs.givewp.com/addon-stripe' )
							);
							?>
						</p>
						<?php
					}
					?>
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
							<input style="width: 400px;" type="text" readonly="true" value="<?php echo site_url() . '/?give-listener=stripe'; ?>"/>
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
						<?php esc_html_e( 'Stripe webhooks are important to setup so GiveWP can communicate properly with the payment gateway. It is not required to have the sandbox webhooks setup unless you are testing. Note: webhooks cannot be setup on localhost or websites in maintenance mode.', 'give' ); ?>
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

			$default_attributes  = array(
				'rows' => 10,
				'cols' => 60,
			);
			$textarea_attributes = isset( $value['attributes'] ) ? $field_options['attributes'] : array();

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
	}
}

new Give_Stripe_Admin_Settings();
