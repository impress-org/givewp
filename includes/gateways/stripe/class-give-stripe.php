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

			add_action( 'admin_notices', array( $this, 'display_old_recurring_compatibility_notice' ) );
			add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );

			/**
			 * Using hardcoded constant for backward compatibility of Give 2.5.0 with Recurring 1.8.13 when Stripe Premium is not active.
			 *
			 * This code will handle extreme rare scenario.
			 *
			 * @since 2.5.0
			 *
			 * @todo Remove this contant declaration after 2-3 Give core minor releases.
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

			require_once GIVE_PLUGIN_DIR . 'vendor/autoload.php';

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

		/**
		 * Display compatibility notice for Give 2.5.0 and Recurring 1.8.13 when Stripe premium is not active.
		 *
		 * @since 2.5.0
		 *
		 * @return void
		 */
		public function display_old_recurring_compatibility_notice() {

			// Bailout early, if Give 2.5.0 and recurring 1.8.13 is compatible.
			if (
				defined( 'GIVE_RECURRING_VERSION' ) &&
				version_compare( GIVE_RECURRING_VERSION, '1.9.0', '>=' )
			) {
				return;
			}

			$message = sprintf(
				__( '<strong>Attention:</strong> Give 2.5.0 requires the latest version of the Recurring donations add-on to process donations properly. Please update to the latest version of Recurring donations add-on to resolve compatibility issues. If your license is active, you should see the update available in WordPress. Otherwise, you can access the latest version by <a href="%1$s" target="_blank">logging into your account</a> and visiting <a href="%1$s" target="_blank">your downloads</a> page on the Give website.', 'give' ),
				esc_url( 'https://givewp.com/wp-login.php' ),
				esc_url( 'https://givewp.com/my-account/#tab_downloads' )
			);

			// Show notice, if incompatibility found.
			Give()->notices->register_notice(
				array(
					'id'          => 'give-compatibility-with-old-recurring',
					'type'        => 'error',
					'description' => $message,
					'show'        => true,
				)
			);

		}
	}
}

new Give_Stripe();
