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
			add_filter( 'give_get_settings_gateways', array( $this, 'register_settings' ) );
			add_action( 'give_admin_field_stripe_connect', array( $this, 'stripe_connect_field' ), 10, 2 );
		}

		/**
		 * Register sections.
		 *
		 * @acess public
		 *
		 * @param $sections
		 *
		 * @return mixed
		 */
		public function register_sections( $sections ) {
			$sections['stripe-settings'] = __( 'Stripe Settings', 'give' );

			return $sections;
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

			switch ( give_get_current_setting_section() ) {

				case 'stripe-settings':
					// Stripe Admin Settings - Header
					$settings = array(
						array(
							'id'   => 'give_title_stripe',
							'type' => 'title',
						),
					);

					// Stripe Admin Settings - Configuration Fields.
					$settings[] = array(
						'name'          => __( 'Stripe Connect', 'give' ),
						'desc'          => '',
						'wrapper_class' => 'give-stripe-connect-tr',
						'id'            => 'stripe_connect',
						'type'          => 'stripe_connect',
					);

					/**
					 * This filter hook is used to add configuration fields like api key, access token, oAuth button, etc.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_configuration_fields', $settings );

					$settings[] = array(
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

					$settings[] = array(
						'name' => __( 'Collect Billing Details', 'give' ),
						'desc' => __( 'This option will enable the billing details section for Stripe which requires the donor\'s address to complete the donation. These fields are not required by Stripe to process the transaction, but you may have the need to collect the data.', 'give' ),
						'id'   => 'stripe_collect_billing',
						'type' => 'checkbox',
					);

					$settings[] = array(
						'name'          => __( 'Credit Card Fields Format', 'give' ),
						'desc'          => __( 'This option will enable you to show single or multiple credit card fields on your donation form for Stripe Payment Gateway.', 'give' ),
						'id'            => 'stripe_cc_fields_format',
						'wrapper_class' => 'stripe-cc-field-format-settings ' . $this->stripe_modal_checkout_status( 'disabled' ),
						'type'          => 'radio_inline',
						'default'       => 'multi',
						'options'       => array(
							'single' => __( 'Single Field', 'give' ),
							'multi'  => __( 'Multi Field', 'give' ),
						),
					);

					/**
					 * This filter hook is used to add fields before Stripe Checkout fields.
					 *
					 * @since 2.5.0
					 *
					 * @return array
					 */
					$settings = apply_filters( 'give_stripe_add_before_checkout_fields', $settings );

					$settings[] = array(
						'name' => __( 'Enable Stripe Checkout', 'give' ),
						'desc' => sprintf( __( 'This option will enable <a href="%s" target="_blank">Stripe\'s modal checkout</a> where the donor will complete the donation rather than the default credit card fields on page. Note: Apple and Google pay do not work with the modal checkout option.', 'give' ), 'http://docs.givewp.com/stripe-checkout' ),
						'id'   => 'stripe_checkout_enabled',
						'type' => 'checkbox',
					);

					$settings[] = array(
						'name'          => __( 'Checkout Heading', 'give' ),
						'desc'          => __( 'This is the main heading within the modal checkout. Typically, this is the name of your organization, cause, or website.', 'give' ),
						'id'            => 'stripe_checkout_name',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => get_bloginfo( 'name' ),
						'type'          => 'text',
					);

					$settings[] = array(
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

					$settings[] = array(
						'name'          => __( 'Processing Text', 'give' ),
						'desc'          => __( 'This text appears briefly after the donor has made a successful donation while Give is confirming the payment with the Stripe API.', 'give' ),
						'id'            => 'stripe_checkout_processing_text',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => __( 'Donation Processing...', 'give' ),
						'type'          => 'text',
					);

					$settings[] = array(
						'name'          => __( 'Verify Zip Code', 'give' ),
						'desc'          => __( 'Specify whether Checkout should validate the billing ZIP code of the donor for added fraud protection.', 'give' ),
						'id'            => 'stripe_checkout_zip_verify',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => 'on',
						'type'          => 'checkbox',
					);

					$settings[] = array(
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

					$settings[] = array(
						'name'  => __( 'Stripe Gateway Documentation', 'give' ),
						'id'    => 'display_settings_docs_link',
						'url'   => esc_url( 'http://docs.givewp.com/addon-stripe' ),
						'title' => __( 'Stripe Gateway Documentation', 'give' ),
						'type'  => 'give_docs_link',
					);

					// Stripe Admin Settings - Footer.
					$settings[] = array(
						'id'   => 'give_title_stripe',
						'type' => 'sectionend',
					);
					break;
			} // End switch().

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

			$stripe_checkout = give_stripe_is_checkout_enabled();

			if (
				( $stripe_checkout && 'disabled' === $status ) ||
				( ! $stripe_checkout && 'enabled' === $status )
			) {
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
					<label for="test_secret_key"> <?php esc_attr_e( 'Stripe Connection', 'give' ); ?></label>
				</th>
				<?php
				if ( give_stripe_is_connected() ) :
					$stripe_user_id = give_get_option( 'give_stripe_user_id' );
					?>

					<td class="give-forminp give-forminp-api_key">
						<span id="give-stripe-connect" class="stripe-btn-disabled"><span>Connected</span></span>
						<p class="give-field-description">
							<span class="dashicons dashicons-yes" style="color:#25802d;"></span>
							<?php
							esc_attr_e( 'Stripe is connected.', 'give' );
							$disconnect_confirmation_message = sprintf(
								/* translators: %s Stripe User ID */
								__( 'Are you sure you want to disconnect Give from Stripe? If disconnected, this website and any others sharing the same Stripe account (%s) that are connected to Give will need to reconnect in order to process payments.', 'give-stripe' ),
								$stripe_user_id
							);
							?>
							<a href="<?php give_stripe_disconnect_url(); ?>" class="give-stripe-disconnect"
								onclick="return confirm('<?php echo esc_html( $disconnect_confirmation_message ); ?>');">[Disconnect]</a>
						</p>
					</td>


				<?php else : ?>
					<td class="give-forminp give-forminp-api_key">
						<?php give_stripe_connect_button(); ?>
						<p class="give-field-description">
							<span class="dashicons dashicons-no"
								style="color:red;"></span><?php _e( 'Stripe is NOT connected.', 'give' ); ?>
						</p>
						<?php if ( isset( $_GET['error_code'] ) && isset( $_GET['error_message'] ) ) : ?>
							<p class="stripe-connect-error">
								<strong><?php echo give_clean( $_GET['error_code'] ); ?>:</strong> <?php echo give_clean( $_GET['error_message'] ); ?>
							</p>
						<?php endif; ?>
					</td>

				<?php endif; ?>

			</tr>
		<?php
		}
	}
}

new Give_Stripe_Admin_Settings();
