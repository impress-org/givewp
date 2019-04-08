<?php
/**
 * Give - Stripe Core Logs
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

if ( ! class_exists( 'Give_Stripe_Logs' ) ) {

	/**
	 * Class Give_Stripe_Logs
	 *
	 * @since 2.5.0
	 */
	class Give_Stripe_Logs {

		/**
		 * Set single instance.
		 *
		 * @since  2.5.0
		 * @access private
		 *
		 * @var Give_Stripe_Logs $instance
		 */
		private static $instance;

		/**
		 * Get single instance of class object.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return Give_Stripe_Logs
		 */
		public static function get_instance() {
			if ( null === static::$instance ) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		/**
		 * Setup hooks.
		 *
		 * @since  2.5.0
		 * @access public
		 */
		public function setup_hooks() {

			// Bailout, if not admin.
			if ( ! is_admin() ) {
				return;
			}

			add_filter( 'give_log_types', array( $this, 'set_stripe_log_type' ) );
			add_filter( 'give_log_views', array( $this, 'set_stripe_log_section' ) );
			add_action( 'give_logs_view_stripe', array( $this, 'give_stripe_logs_view' ) );
		}

		/**
		 * This function will set new stripe log type as valid log type.
		 *
		 * @param array $types List of log types.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return array
		 */
		public function set_stripe_log_type( $types ) {

			$new_log_type = array( 'stripe' );

			return array_merge( $types, $new_log_type );
		}

		/**
		 * This function will set new stripe log section.
		 *
		 * @param array $sections List of log sections.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return array
		 */
		public function set_stripe_log_section( $sections ) {

			$new_log_section = array(
				'stripe' => __( 'Stripe', 'give' ),
			);

			return array_merge( $sections, $new_log_section );
		}


		/**
		 * Stripe Logs View
		 *
		 * @since 2.5.0
		 *
		 * @return void
		 */
		public function give_stripe_logs_view() {
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/class-give-stripe-logs-list-table.php';

			$logs_table = new Give_Stripe_Log_Table();
			$logs_table->prepare_items();
			?>
			<div class="wrap">

				<?php
				/**
				 * Fires before displaying Payment Error logs.
				 *
				 * @since 2.0.8
				 */
				do_action( 'give_stripe_logs_top' );

				$logs_table->display();
				?>
				<input type="hidden" name="post_type" value="give_forms"/>
				<input type="hidden" name="page" value="give-tools"/>
				<input type="hidden" name="tab" value="logs"/>
				<input type="hidden" name="section" value="stripe"/>

				<?php
				/**
				 * Fires after displaying update logs.
				 *
				 * @since 2.0.8
				 */
				do_action( 'give_stripe_logs_bottom' );
				?>

			</div>
			<?php
		}

	}
}

Give_Stripe_Logs::get_instance()->setup_hooks();
