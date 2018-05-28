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

			// Do not use main form for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				add_action( 'give_admin_field_enabled_gateways', array( $this, 'render_enabled_gateways' ), 10, 2 );
			}
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
						// Section 2: PayPal Standard.
						array(
							'type' => 'title',
							'id'   => 'give_title_gateway_settings_2',
						),
						array(
							'name' => __( 'PayPal Email', 'give' ),
							'desc' => __( 'Enter your PayPal account\'s email.', 'give' ),
							'id'   => 'paypal_email',
							'type' => 'email',
						),
						array(
							'name' => __( 'PayPal Page Style', 'give' ),
							'desc' => __( 'Enter the name of the PayPal page style to use, or leave blank to use the default.', 'give' ),
							'id'   => 'paypal_page_style',
							'type' => 'text',
						),
						array(
							'name'    => __( 'PayPal Transaction Type', 'give' ),
							'desc'    => __( 'Nonprofits must verify their status to withdraw donations they receive via PayPal. PayPal users that are not verified nonprofits must demonstrate how their donations will be used, once they raise more than $10,000. By default, Give transactions are sent to PayPal as donations. You may change the transaction type using this option if you feel you may not meet PayPal\'s donation requirements.', 'give' ),
							'id'      => 'paypal_button_type',
							'type'    => 'radio_inline',
							'options' => array(
								'donation' => __( 'Donation', 'give' ),
								'standard' => __( 'Standard Transaction', 'give' )
							),
							'default' => 'donation',
						),
						array(
							'name'    => __( 'Billing Details', 'give' ),
							'desc'    => __( 'This option will enable the billing details section for PayPal Standard which requires the donor\'s address to complete the donation. These fields are not required by PayPal to process the transaction, but you may have a need to collect the data.', 'give' ),
							'id'      => 'paypal_standard_billing_details',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'name'    => __( 'PayPal IPN Verification', 'give' ),
							'desc'    => __( 'If donations are not getting marked as complete, use a slightly less secure method of verifying donations.', 'give' ),
							'id'      => 'paypal_verification',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'name'  => __( 'PayPal Standard Gateway Settings Docs Link', 'give' ),
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
							'name'    => __( 'Collect Billing Details', 'give' ),
							'desc'    => __( 'Enable to request billing details for offline donations. Will appear above offline donation instructions. Can be enabled/disabled per form.', 'give' ),
							'id'      => 'give_offline_donation_enable_billing_fields',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' )
							)
						),
						array(
							'name'    => __( 'Offline Donation Instructions', 'give' ),
							'desc'    => __( 'The following content will appear for all forms when the user selects the offline donation payment option. Note: You may customize the content per form as needed.', 'give' ),
							'id'      => 'global_offline_donation_content',
							'default' => give_get_default_offline_donation_content(),
							'type'    => 'wysiwyg',
							'options' => array(
								'textarea_rows' => 6,
							)
						),
						array(
							'name'  => esc_html__( 'Offline Donations Settings Docs Link', 'give' ),
							'id'    => 'offline_gateway_settings_docs_link',
							'url'   => esc_url( 'http://docs.givewp.com/offlinegateway' ),
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
							'name'    => __( 'Test Mode', 'give' ),
							'desc'    => __( 'While in test mode no live donations are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'give' ),
							'id'      => 'test_mode',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'name' => __( 'Enabled Gateways', 'give' ),
							'desc' => __( 'Enable your payment gateway. Can be ordered by dragging.', 'give' ),
							'id'   => 'gateways',
							'type' => 'enabled_gateways'
						),

						/**
						 * "Enabled Gateways" setting field contains gateways label setting but when you save gateway settings then label will not save
						 *  because this is not registered setting API and code will not recognize them.
						 *
						 * This setting will not render on admin setting screen but help internal code to recognize "gateways_label"  setting and add them to give setting when save.
						 */
						array(
							'name' => __( 'Gateways Label', 'give' ),
							'desc' => '',
							'id'   => 'gateways_label',
							'type' => 'gateways_label_hidden'
						),

						/**
						 * "Enabled Gateways" setting field contains default gateway setting but when you save gateway settings then this setting will not save
						 *  because this is not registered setting API and code will not recognize them.
						 *
						 * This setting will not render on admin setting screen but help internal code to recognize "default_gateway"  setting and add them to give setting when save.
						 */
						array(
							'name' => __( 'Default Gateway', 'give' ),
							'desc' => __( 'The gateway that will be selected by default.', 'give' ),
							'id'   => 'default_gateway',
							'type' => 'default_gateway_hidden'
						),

						array(
							'name'  => __( 'Gateways Docs Link', 'give' ),
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
				'gateways-settings' => __( 'Gateways', 'give' ),
				'paypal-standard'   => __( 'PayPal Standard', 'give' ),
				'offline-donations' => __( 'Offline Donations', 'give' )
			);

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}


		/**
		 * Render enabled gateways
		 *
		 * @since  2.0.5
		 * @access public
		 *
		 * @param $field
		 * @param $settings
		 */
		public function render_enabled_gateways( $field, $settings ) {
			$id              = $field['id'];
			$gateways        = give_get_ordered_payment_gateways( give_get_payment_gateways() );
			$gateways_label  = give_get_option( 'gateways_label', array() );
			$default_gateway = give_get_option( 'default_gateway', current( array_keys( $gateways ) ) );

			ob_start();

			echo '<div class="gateway-enabled-wrap">';

			echo '<div class="gateway-enabled-settings-title">';
			printf(
				'
						<span></span>
						<span>%1$s</span>
						<span>%2$s</span>
						<span>%3$s</span>
						<span>%4$s</span>
						',
				__( 'Gateway', 'give' ),
				__( 'Label', 'give' ),
				__( 'Default', 'give' ),
				__( 'Enabled', 'give' )
			);
			echo '</div>';

			echo '<ul class="give-checklist-fields give-payment-gatways-list">';
			foreach ( $gateways as $key => $option ) :
				$enabled = null;
				if ( is_array( $settings ) && array_key_exists( $key, $settings ) ) {
					$enabled = '1';
				}

				echo '<li>';
				printf( '<span class="give-drag-handle"><span class="dashicons dashicons-menu"></span></span>' );
				printf( '<span class="admin-label">%s</span>', esc_html( $option['admin_label'] ) );

				$label = '';
				if ( ! empty( $gateways_label[ $key ] ) ) {
					$label = $gateways_label[ $key ];
				}

				printf(
					'<input class="checkout-label" type="text" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%3$s" placeholder="%4$s"/>',
					'gateways_label',
					esc_attr( $key ),
					esc_html( $label ),
					esc_html( $option['checkout_label'] )
				);

				printf(
					'<input class="gateways-radio" type="radio" name="%1$s" value="%2$s" %3$s %4$s>',
					'default_gateway',
					$key,
					checked( $key, $default_gateway, false ),
					disabled( NULL, $enabled, false )
				);

				printf(
					'<input class="gateways-checkbox" name="%1$s[%2$s]" id="%1$s[%2$s]" type="checkbox" value="1" %3$s data-payment-gateway="%4$s"/>',
					esc_attr( $id ),
					esc_attr( $key ),
					checked( '1', $enabled, false ),
					esc_html( $option['admin_label'] )
				);
				echo '</li>';
			endforeach;
			echo '</ul>';

			echo '</div>'; // end gateway-enabled-wrap.

			printf(
				'<tr><th>%1$s</th><td>%2$s</td></tr>',
				$field['title'],
				ob_get_clean()
			);
		}
	}

endif;

return new Give_Settings_Gateways();
