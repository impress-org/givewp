<?php
/**
 * Give - Stripe Core
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

if ( ! class_exists( 'Give_Stripe' ) ) {

	class Give_Stripe {

		/**
		 * Give_Stripe() constructor.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function __construct() {

			global $give_stripe;

			add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );

			/**
			 * Using hardcoded constant for backward compatibility of Give 2.5.0 with Recurring 1.8.13 when Stripe Premium is not active.
			 *
			 * This code will handle extreme rare scenario.
			 *
			 * @since 2.5.0
			 *
			 * @todo Remove this constant declaration after 2-3 Give core minor releases.
			 */
			if ( ! defined( 'GIVE_STRIPE_BASENAME' ) ) {
				define( 'GIVE_STRIPE_BASENAME', 'give-stripe/give-stripe.php' );
			}

			$this->includes();
		}

		/**
		 * This function is used to include the related Stripe core files.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function includes() {

			// Load Stripe SDK.
			$stripe_sdk_compatibility = give_get_option( 'stripe_sdk_incompatibility', 'composer' );

			if ( 'composer' === $stripe_sdk_compatibility ) {
				require_once GIVE_PLUGIN_DIR . 'vendor/autoload.php';
			} elseif ( 'manual' === $stripe_sdk_compatibility ) {
				require_once GIVE_PLUGIN_DIR . 'vendor/stripe/stripe-php/init.php';
			}

			// Include admin files.
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/admin-actions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/admin-filters.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/class-give-stripe-admin-settings.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/class-give-stripe-logs.php';

			// Include frontend files.
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/give-stripe-helpers.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/actions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-logger.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-invoice.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-customer.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-payment-intent.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-payment-method.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-gateway.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-webhooks.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/payment-methods/class-give-stripe-card.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/give-stripe-scripts.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/deprecated/deprecated-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/deprecated/deprecated-filters.php';

		}

		/**
		 * Register the payment methods supported by Stripe.
		 *
		 * @access public
		 * @since  2.5.0
		 *
		 * @param array $gateways List of registered gateways.
		 *
		 * @return array
		 */
		public function register_gateway( $gateways ) {

			$gateways['stripe'] = array(
				'admin_label'    => __( 'Stripe - Credit Card', 'give' ),
				'checkout_label' => __( 'Credit Card', 'give' ),
			);

			return $gateways;
		}
	}
}

new Give_Stripe();
