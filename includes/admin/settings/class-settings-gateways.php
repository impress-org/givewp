<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Gateways
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Gateways' ) ) :

	/**
	 * Give_Settings_Gateways.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Gateways extends Give_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'gateways';
			$this->label = esc_html__( 'Payment Gateways', 'give' );

			$this->default_tab = 'gateways-settings';

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
				case 'paypal-standard':
					$settings = array(
						// Section 2: Paypal Standard.
						array(
							'type' => 'title',
							'id'   => 'give_title_gateway_settings_2',
						),
						array(
							'name' => esc_html__( 'PayPal Email', 'give' ),
							'desc' => esc_html__( 'Enter your PayPal account\'s email.', 'give' ),
							'id'   => 'paypal_email',
							'type' => 'email',
						),
						array(
							'name' => esc_html__( 'PayPal Page Style', 'give' ),
							'desc' => esc_html__( 'Enter the name of the PayPal page style to use, or leave blank to use the default.', 'give' ),
							'id'   => 'paypal_page_style',
							'type' => 'text',
						),
						array(
							'name'    => esc_html__( 'PayPal Transaction Type', 'give' ),
							'desc'    => esc_html__( 'Nonprofits must verify their status to withdraw donations they receive via PayPal. PayPal users that are not verified nonprofits must demonstrate how their donations will be used, once they raise more than $10,000. By default, Give transactions are sent to PayPal as donations. You may change the transaction type using this option if you feel you may not meet PayPal\'s donation requirements.', 'give' ),
							'id'      => 'paypal_button_type',
							'type'    => 'radio_inline',
							'options' => array(
								'donation' => esc_html__( 'Donation', 'give' ),
								'standard' => esc_html__( 'Standard Transaction', 'give' )
							),
							'default' => 'donation',
						),
						array(
							'name'    => esc_html__( 'PayPal IPN Verification', 'give' ),
							'desc'    => esc_html__( 'If donations are not getting marked as complete, use a slightly less secure method of verifying donations.', 'give' ),
							'id'      => 'paypal_verification',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
                        array(
                            'name'  => esc_html__( 'PayPal Standard Gateway Settings Docs Link', 'give' ),
                            'id'    => 'paypal_standard_gateway_settings_docs_link',
                            'url'   => esc_url( 'http://docs.givewp.com/settings-gateway-paypal-standard' ),
                            'title' => __( 'PayPal Standard Gateway Settings', 'give' ),
                            'type'  => 'give_docs_link',
                        ),
						array(
							'type' => 'sectionend',
							'id'   => 'give_title_gateway_settings_2',
						)
					);
					break;

				case 'offline-donations' :
					$settings = array(
						// Section 3: Offline gateway.
						array(
							'type' => 'title',
							'id'   => 'give_title_gateway_settings_3',
						),
						array(
							'name'    => esc_html__( 'Collect Billing Details', 'give' ),
							'desc'    => esc_html__( 'Enable to request billing details for offline donations. Will appear above offline donation instructions. Can be enabled/disabled per form.', 'give' ),
							'id'      => 'give_offline_donation_enable_billing_fields',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' )
							)
						),
						array(
							'name'    => esc_html__( 'Offline Donation Instructions', 'give' ),
							'desc'    => esc_html__( 'The following content will appear for all forms when the user selects the offline donation payment option. Note: You may customize the content per form as needed.', 'give' ),
							'id'      => 'global_offline_donation_content',
							'default' => give_get_default_offline_donation_content(),
							'type'    => 'wysiwyg',
							'options' => array(
								'textarea_rows' => 6,
							)
						),
						array(
							'name'    => esc_html__( 'Offline Donation Email Instructions Subject', 'give' ),
							'desc'    => esc_html__( 'Enter the subject line for the donation receipt email.', 'give' ),
							'id'      => 'offline_donation_subject',
							'default' => esc_attr__( '{donation} - Offline Donation Instructions', 'give' ),
							'type'    => 'text'
						),
						array(
							'name'    => esc_html__( 'Offline Donation Email Instructions', 'give' ),
							'desc'    => esc_html__( 'Enter the instructions you want emailed to the donor after they have submitted the donation form. Most likely this would include important information like mailing address and who to make the check out to.', 'give' ),
							'id'      => 'global_offline_donation_email',
							'default' => give_get_default_offline_donation_email_content(),
							'type'    => 'wysiwyg',
							'options' => array(
								'textarea_rows' => 6,
							)
						),
                        array(
                            'name'  => esc_html__( 'Offline Donations Settings Docs Link', 'give' ),
                            'id'    => 'offline_gateway_settings_docs_link',
                            'url'   => esc_url( 'http://docs.givewp.com/settings-gateway-offline-donations' ),
                            'title' => __( 'Offline Gateway Settings', 'give' ),
                            'type'  => 'give_docs_link',
                        ),
						array(
							'type' => 'sectionend',
							'id'   => 'give_title_gateway_settings_3',
						)
					);
					break;

				case 'gateways-settings':
					$settings = array(
						// Section 1: Gateways.
						array(
							'id'   => 'give_title_gateway_settings_1',
							'type' => 'title'
						),
						array(
							'name'    => esc_html__( 'Test Mode', 'give' ),
							'desc'    => esc_html__( 'While in test mode no live donations are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'give' ),
							'id'      => 'test_mode',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'name' => esc_html__( 'Enabled Gateways', 'give' ),
							'desc' => esc_html__( 'Enable your payment gateway. Can be ordered by dragging.', 'give' ),
							'id'   => 'gateways',
							'type' => 'enabled_gateways'
						),
						array(
							'name' => esc_html__( 'Default Gateway', 'give' ),
							'desc' => esc_html__( 'The gateway that will be selected by default.', 'give' ),
							'id'   => 'default_gateway',
							'type' => 'default_gateway'
						),
                        array(
                            'name'  => esc_html__( 'Gateways Docs Link', 'give' ),
                            'id'    => 'gateway_settings_docs_link',
                            'url'   => esc_url( 'http://docs.givewp.com/settings-gateways' ),
                            'title' => __( 'Gateway Settings', 'give' ),
                            'type'  => 'give_docs_link',
                        ),
						array(
							'id'   => 'give_title_gateway_settings_1',
							'type' => 'sectionend'
						),
					);
					break;
			}

			/**
			 * Filter the payment gateways settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'give_settings_gateways', $settings );

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
				'gateways-settings' => esc_html__( 'Gateways', 'give' ),
				'paypal-standard'   => esc_html__( 'Paypal Standard', 'give' ),
				'offline-donations' => esc_html__( 'Offline', 'give' )
			);

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}
	}

endif;

return new Give_Settings_Gateways();
