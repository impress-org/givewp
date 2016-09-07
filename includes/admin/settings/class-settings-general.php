<?php
/**
 * Give General Settings
 *
 * @author      WordImpress
 * @version     1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_General' ) ) :

	/**
	 * Give_Admin_Settings_General.
	 */
	class Give_Settings_General extends Give_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'general';
			$this->label = __( 'General', 'give' );

			add_filter( 'give_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'give_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'give_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings() {

			$currency_code_options = give_get_currencies();

			foreach ( $currency_code_options as $code => $name ) {
				$currency_code_options[ $code ] = $name . ' (' . $code. ')';
			}

			$settings = apply_filters( 'give_general_settings', array(

				array( 'title' => __( 'General Options', 'give' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

				array(
					'title'    => __( 'Base Location', 'give' ),
					'desc'     => __( 'This is the base location for your business. Tax rates will be based on this country.', 'give' ),
					'id'       => 'give_default_country',
					'css'      => 'min-width:350px;',
					'default'  => 'GB',
					'type'     => 'single_select_country',
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Selling Location(s)', 'give' ),
					'desc'     => __( 'This option lets you limit which countries you are willing to sell to.', 'give' ),
					'id'       => 'give_allowed_countries',
					'default'  => 'all',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width: 350px;',
					'desc_tip' => true,
					'options'  => array(
						'all'        => __( 'Sell to All Countries', 'give' ),
						'all_except' => __( 'Sell to All Countries, Except For&hellip;', 'give' ),
						'specific'   => __( 'Sell to Specific Countries', 'give' ),
					),
				),

				array(
					'title'   => __( 'Sell to All Countries, Except For&hellip;', 'give' ),
					'desc'    => '',
					'id'      => 'give_all_except_countries',
					'css'     => 'min-width: 350px;',
					'default' => '',
					'type'    => 'multi_select_countries',
				),

				array(
					'title'   => __( 'Sell to Specific Countries', 'give' ),
					'desc'    => '',
					'id'      => 'give_specific_allowed_countries',
					'css'     => 'min-width: 350px;',
					'default' => '',
					'type'    => 'multi_select_countries',
				),

				array(
					'title'    => __( 'Shipping Location(s)', 'give' ),
					'desc'     => __( 'Choose which countries you want to ship to, or choose to ship to all locations you sell to.', 'give' ),
					'id'       => 'give_ship_to_countries',
					'default'  => '',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'desc_tip' => true,
					'options'  => array(
						''         => __( 'Ship to all countries you sell to', 'give' ),
						'all'      => __( 'Ship to all countries', 'give' ),
						'specific' => __( 'Ship to specific countries only', 'give' ),
						'disabled' => __( 'Disable shipping &amp; shipping calculations', 'give' ),
					),
				),

				array(
					'title'   => __( 'Ship to Specific Countries', 'give' ),
					'desc'    => '',
					'id'      => 'give_specific_ship_to_countries',
					'css'     => '',
					'default' => '',
					'type'    => 'multi_select_countries',
				),

				array(
					'title'    => __( 'Default Customer Location', 'give' ),
					'id'       => 'give_default_customer_address',
					'desc_tip' => __( 'This option determines a customers default location. The MaxMind GeoLite Database will be periodically downloaded to your wp-content directory if using geolocation.', 'give' ),
					'default'  => 'geolocation',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'options'  => array(
						''                 => __( 'No location by default', 'give' ),
						'base'             => __( 'Shop base address', 'give' ),
						'geolocation'      => __( 'Geolocate', 'give' ),
						'geolocation_ajax' => __( 'Geolocate (with page caching support)', 'give' ),
					),
				),

				array(
					'title'   => __( 'Enable Taxes', 'give' ),
					'desc'    => __( 'Enable taxes and tax calculations', 'give' ),
					'id'      => 'give_calc_taxes',
					'default' => 'no',
					'type'    => 'checkbox',
				),

				array(
					'title'   => __( 'Store Notice', 'give' ),
					'desc'    => __( 'Enable site-wide store notice text', 'give' ),
					'id'      => 'give_demo_store',
					'default' => 'no',
					'type'    => 'checkbox',
				),

				array(
					'title'    => __( 'Store Notice Text', 'give' ),
					'desc'     => '',
					'id'       => 'give_demo_store_notice',
					'default'  => __( 'This is a demo store for testing purposes &mdash; no orders shall be fulfilled.', 'give' ),
					'type'     => 'textarea',
					'css'     => 'width:350px; height: 65px;',
					'autoload' => false,
				),

				array( 'type' => 'sectionend', 'id' => 'general_options' ),

				array( 'title' => __( 'Currency Options', 'give' ), 'type' => 'title', 'desc' => __( 'The following options affect how prices are displayed on the frontend.', 'give' ), 'id' => 'pricing_options' ),

				array(
					'title'    => __( 'Currency', 'give' ),
					'desc'     => __( 'This controls what currency prices are listed at in the catalog and which currency gateways will take payments in.', 'give' ),
					'id'       => 'give_currency',
					'css'      => 'min-width:350px;',
					'default'  => 'GBP',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'desc_tip' => true,
					'options'  => $currency_code_options,
				),

				array(
					'title'    => __( 'Currency Position', 'give' ),
					'desc'     => __( 'This controls the position of the currency symbol.', 'give' ),
					'id'       => 'give_currency_pos',
					'css'      => 'min-width:350px;',
					'class'    => 'wc-enhanced-select',
					'default'  => 'left',
					'type'     => 'select',
					'options'  => array(
						'left'        => __( 'Left', 'give' ) . ' (' . give_get_currency() . '99.99)',
						'right'       => __( 'Right', 'give' ) . ' (99.99' . give_get_currency() . ')',
						'left_space'  => __( 'Left with space', 'give' ) . ' (' . give_get_currency() . ' 99.99)',
						'right_space' => __( 'Right with space', 'give' ) . ' (99.99 ' . give_get_currency() . ')',
					),
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Thousand Separator', 'give' ),
					'desc'     => __( 'This sets the thousand separator of displayed prices.', 'give' ),
					'id'       => 'give_price_thousand_sep',
					'css'      => 'width:50px;',
					'default'  => ',',
					'type'     => 'text',
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Decimal Separator', 'give' ),
					'desc'     => __( 'This sets the decimal separator of displayed prices.', 'give' ),
					'id'       => 'give_price_decimal_sep',
					'css'      => 'width:50px;',
					'default'  => '.',
					'type'     => 'text',
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Number of Decimals', 'give' ),
					'desc'     => __( 'This sets the number of decimal points shown in displayed prices.', 'give' ),
					'id'       => 'give_price_num_decimals',
					'css'      => 'width:50px;',
					'default'  => '2',
					'desc_tip' => true,
					'type'     => 'number',
					'custom_attributes' => array(
						'min'  => 0,
						'step' => 1,
					),
				),

				array( 'type' => 'sectionend', 'id' => 'pricing_options' ),

			) );

			return apply_filters( 'give_get_settings_' . $this->id, $settings );
		}

		/**
		 * Output a colour picker input box.
		 *
		 * @param mixed $name
		 * @param string $id
		 * @param mixed $value
		 * @param string $desc (default: '')
		 */
		public function color_picker( $name, $id, $value, $desc = '' ) {
			echo '<div class="color_box">' . wc_help_tip( $desc ) . '
			<input name="' . esc_attr( $id ). '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div>
		</div>';
		}

		/**
		 * Save settings.
		 */
		public function save() {
			$settings = $this->get_settings();

			Give_Admin_Settings::save_fields( $settings, 'give-settings-2' );
		}
	}

endif;

return new Give_Settings_General();
