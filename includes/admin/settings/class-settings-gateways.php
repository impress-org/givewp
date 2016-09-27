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
			$this->id    = 'gateways';
			$this->label = esc_html__( 'Payment Gateways', 'give' );

			add_filter( 'give_default_setting_tab_section_gateways', array( $this, 'set_default_setting_tab' ), 10 );
			add_filter( 'give_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( "give_sections_{$this->id}_page", array( $this, 'output_sections' ) );
			add_action( "give_settings_{$this->id}_page", array( $this, 'output' ) );
			add_action( "give_settings_save_{$this->id}", array( $this, 'save' ) );
		}


		/**
		 * Deafault setting tab.
		 *
		 * @since  1.8
		 * @param  $setting_tab
		 * @return string
		 */
		function set_default_setting_tab( $setting_tab ) {
			return 'gateways';
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
							'type' => 'text_email',
						),
						array(
							'name' => esc_html__( 'PayPal Page Style', 'give' ),
							'desc' => esc_html__( 'Enter the name of the page style to use, or leave blank to use the default.', 'give' ),
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
							'name' => esc_html__( 'Disable PayPal IPN Verification', 'give' ),
							'desc' => esc_html__( 'If donations are not getting marked as complete, use a slightly less secure method of verifying donations.', 'give' ),
							'id'   => 'disable_paypal_verification',
							'type' => 'checkbox'
						),
						array(
							'type' => 'sectionend',
							'id'   => 'give_title_gateway_settings_2',
						)
					);
					break;

				case 'offline' :
					$settings = array(
						// Section 3: Offline gateway.
						array(
							'type' => 'title',
							'id'   => 'give_title_gateway_settings_3',
						),
						array(
							'name' => esc_html__( 'Collect Billing Details', 'give' ),
							'desc' => esc_html__( 'Enable to request billing details for offline donations. Will appear above offline donation instructions. Can be enabled/disabled per form.', 'give' ),
							'id'   => 'give_offline_donation_enable_billing_fields',
							'type' => 'checkbox'
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
							'type' => 'sectionend',
							'id'   => 'give_title_gateway_settings_3',
						)
					);
					break;

				case 'gateways':
					$settings = array(
						// Section 1: Gateways.
						array(
							'id'   => 'give_title_gateway_settings_1',
							'type' => 'title'
						),
						array(
							'name' => esc_html__( 'Test Mode', 'give' ),
							'desc' => esc_html__( 'While in test mode no live donations are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'give' ),
							'id'   => 'test_mode',
							'type' => 'checkbox'
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
							'id'   => 'give_title_gateway_settings_1',
							'type' => 'sectionend'
						),
					);
					break;

				default:
					/**
					 * Filter the payment gateways settings.
					 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
					 */
					$settings = apply_filters( 'give_settings_gateways', $settings );
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
				'gateways'         => esc_html__( 'Gateways', 'give' ),
				'paypal-standard'  => esc_html__( 'Paypal Standard', 'give' ),
				'offline'          => esc_html__( 'Offline', 'give' )
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

return new Give_Settings_Gateways();
