<?php
/**
 * Give - Stripe Core Admin SEPA Settings
 *
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, Florian Backmeier
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Stripe_Admin_SEPA_Settings' ) ) {
	/**
	 * Class Give_Stripe_Admin_SEPA_Settings
	 *
	 */
	class Give_Stripe_Admin_SEPA_Settings {

		/**
		 * Give_Stripe_Admin_SEPA_Settings() constructor.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function __construct() {
			// Bailout, if not accessed via admin.
			if ( ! is_admin() ) {
				return;
			}

			add_filter( 'give_stripe_register_groups', array( $this, 'register_groups' ) );
			add_filter( 'give_stripe_add_additional_group_fields', array( $this, 'register_settings' ) );
		}

		/**
		 * Register groups of a section.
		 *
		 * @since  2.6.0
		 * @access public
		 *
		 * @return array
		 */
		public function register_groups($groups) {
			$groups['sepa_debit'] = __( 'SEPA Debit', 'give' );

			return $groups;
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
					$settings = apply_filters( 'give_stripe_add_before_sepa_fields', $settings );

					$settings['sepa_debit'][] = array(
						'name'          => __( 'Mandate Authorization', 'give' ),
						'desc'          => __( 'Your customer must read and accept the SEPA Direct Debit mandate.', 'give' ),
						'id'            => 'stripe_sepa_mandate',
						'wrapper_class' => 'stripe-sepa_mandate_wrapper',
						'type'          => 'textarea'
					);

					$settings = apply_filters( 'give_stripe_add_after_sepa_fields', $settings );

					break;
			}

			return $settings;
		}
	}
}

new Give_Stripe_Admin_SEPA_Settings();
