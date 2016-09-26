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
			$this->label = esc_html__( 'General Settings', 'give' );

			add_filter( 'give_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( "give_sections_{$this->id}_page", array( $this, 'output_sections' ) );
			add_action( "give_settings_{$this->id}_page", array( $this, 'output' ) );
			add_action( "give_settings_save_{$this->id}", array( $this, 'save' ) );
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
			global $current_section;

			$settings = array();

			switch ( $current_section ) {
				case 'currency' :
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
						),
						array(
							'name'    => esc_html__( 'Decimal Separator', 'give' ),
							'desc'    => esc_html__( 'The symbol (usually , or .) to separate decimal points.', 'give' ),
							'id'      => 'decimal_separator',
							'type'    => 'text',
							'default' => '.',
						),
						array(
							'name'            => __( 'Number of Decimals', 'give' ),
							'desc'            => __( 'The number of decimal points displayed in amounts.', 'give' ),
							'id'              => 'number_decimals',
							'type'            => 'text',
							'default'         => 2,
							'sanitization_cb' => 'give_sanitize_number_decimals',
						),
						array(
							'type' => 'title',
							'id'   => 'give_title_general_settings_2'
						)
					);
					break;

				default:
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
			}

			/**
			 * Filter the general settings.
			 */
			$settings = apply_filters( 'give_settings_general', $settings );

			// Output.
			return apply_filters( 'give_get_settings_' . $this->id, $settings );
		}

		/**
		 * Get sections.
		 *
		 * @since 1.8
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'general'  => esc_html__( 'General', 'give' ),
				'currency' => esc_html__( 'Currency', 'give' )
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
			global $current_section;

			// Set default section if current section is not set.
			$current_section = empty( $current_section ) ? 'general' : $current_section;

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
