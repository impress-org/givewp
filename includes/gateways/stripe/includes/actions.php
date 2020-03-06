<?php
/**
 * Give - Stripe Frontend Actions
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

/**
 * Stripe uses it's own credit card form because the card details are tokenized.
 *
 * We don't want the name attributes to be present on the fields in order to
 * prevent them from getting posted to the server.
 *
 * @param int  $form_id Donation Form ID.
 * @param int  $args    Donation Form Arguments.
 * @param bool $echo    Status to display or not.
 *
 * @access public
 * @since  1.0
 *
 * @return string $form
 */
function give_stripe_credit_card_form( $form_id, $args, $echo = true ) {

	$id_prefix              = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';
	$publishable_key        = give_stripe_get_publishable_key();
	$secret_key             = give_stripe_get_secret_key();
	$stripe_cc_field_format = give_get_option( 'stripe_cc_fields_format', 'multi' );

	ob_start();

	do_action( 'give_before_cc_fields', $form_id ); ?>

	<fieldset id="give_cc_fields" class="give-do-validate">
		<legend>
			<?php esc_attr_e( 'Credit Card Info', 'give' ); ?>
		</legend>

		<?php
		if ( is_ssl() ) {
			?>
			<div id="give_secure_site_wrapper">
				<span class="give-icon padlock"></span>
				<span>
					<?php esc_attr_e( 'This is a secure SSL encrypted payment.', 'give' ); ?>
				</span>
			</div>
			<?php
		}

		if (
				! is_ssl() &&
				! give_is_test_mode() &&
				(
					empty( $publishable_key ) ||
					empty( $secret_key )
				)
			) {
					Give()->notices->print_frontend_notice(
						sprintf(
							'<strong>%1$s</strong> %2$s',
							esc_html__( 'Notice:', 'give' ),
							esc_html__( 'Credit card fields are disabled because Stripe is not connected and your site is not running securely over HTTPS.', 'give' )
						)
					);
		} elseif (
			empty( $publishable_key ) ||
			empty( $secret_key )
		) {
			Give()->notices->print_frontend_notice(
				sprintf(
					'<strong>%1$s</strong> %2$s',
					esc_html__( 'Notice:', 'give' ),
					esc_html__( 'Credit card fields are disabled because Stripe is not connected.', 'give' )
				)
			);
		} elseif ( ! is_ssl() && ! give_is_test_mode() ) {
			Give()->notices->print_frontend_notice(
				sprintf(
					'<strong>%1$s</strong> %2$s',
					esc_html__( 'Notice:', 'give' ),
					esc_html__( 'Credit card fields are disabled because your site is not running securely over HTTPS.', 'give' )
				)
			);
		} else {
			if ( 'single' === $stripe_cc_field_format ) {

				// Display the stripe container which can be occupied by Stripe for CC fields.
				echo '<div id="give-stripe-single-cc-fields-' . esc_html( $id_prefix ) . '" class="give-stripe-single-cc-field-wrap"></div>';

			} elseif ( 'multi' === $stripe_cc_field_format ) {
				?>
				<div id="give-card-number-wrap" class="form-row form-row-two-thirds form-row-responsive give-stripe-cc-field-wrap">
					<div>
						<label for="give-card-number-field-<?php echo esc_html( $id_prefix ); ?>" class="give-label">
							<?php esc_attr_e( 'Card Number', 'give' ); ?>
							<span class="give-required-indicator">*</span>
							<span class="give-tooltip give-icon give-icon-question"
								data-tooltip="<?php esc_attr_e( 'The (typically) 16 digits on the front of your credit card.', 'give' ); ?>"></span>
							<span class="card-type"></span>
						</label>
						<div id="give-card-number-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty give-stripe-cc-field give-stripe-card-number-field"></div>
					</div>
				</div>

				<div id="give-card-cvc-wrap" class="form-row form-row-one-third form-row-responsive give-stripe-cc-field-wrap">
					<div>
						<label for="give-card-cvc-field-<?php echo esc_html( $id_prefix ); ?>" class="give-label">
							<?php esc_attr_e( 'CVC', 'give' ); ?>
							<span class="give-required-indicator">*</span>
							<span class="give-tooltip give-icon give-icon-question"
								data-tooltip="<?php esc_attr_e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'give' ); ?>"></span>
						</label>
						<div id="give-card-cvc-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty give-stripe-cc-field give-stripe-card-cvc-field"></div>
					</div>
				</div>

				<div id="give-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive">
					<label for="card_name" class="give-label">
						<?php esc_attr_e( 'Cardholder Name', 'give' ); ?>
						<span class="give-required-indicator">*</span>
						<span class="give-tooltip give-icon give-icon-question"
							data-tooltip="<?php esc_attr_e( 'The name of the credit card account holder.', 'give' ); ?>"></span>
					</label>
					<input
						type="text"
						autocomplete="off"
						id="card_name"
						name="card_name"
						class="card-name give-input required"
						placeholder="<?php esc_attr_e( 'Cardholder Name', 'give' ); ?>"
					/>
				</div>

				<?php do_action( 'give_before_cc_expiration' ); ?>

				<div id="give-card-expiration-wrap" class="card-expiration form-row form-row-one-third form-row-responsive give-stripe-cc-field-wrap">
					<div>
						<label for="give-card-expiration-field-<?php echo esc_html( $id_prefix ); ?>" class="give-label">
							<?php esc_attr_e( 'Expiration', 'give' ); ?>
							<span class="give-required-indicator">*</span>
							<span class="give-tooltip give-icon give-icon-question"
								data-tooltip="<?php esc_attr_e( 'The date your credit card expires, typically on the front of the card.', 'give' ); ?>"></span>
						</label>

						<div id="give-card-expiration-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty give-stripe-cc-field give-stripe-card-expiration-field"></div>
					</div>
				</div>
				<?php
			} // End if().

			/**
			 * This action hook is used to display content after the Credit Card expiration field.
			 *
			 * Note: Kept this hook as it is.
			 *
			 * @since 2.5.0
			 *
			 * @param int   $form_id Donation Form ID.
			 * @param array $args    List of additional arguments.
			 */
			do_action( 'give_after_cc_expiration', $form_id, $args );

			/**
			 * This action hook is used to display content after the Credit Card expiration field.
			 *
			 * @since 2.5.0
			 *
			 * @param int   $form_id Donation Form ID.
			 * @param array $args    List of additional arguments.
			 */
			do_action( 'give_stripe_after_cc_expiration', $form_id, $args );
		}
		?>
	</fieldset>
	<?php
	// Remove Address Fields if user has option enabled.
	$billing_fields_enabled = give_get_option( 'stripe_collect_billing' );
	if ( ! $billing_fields_enabled ) {
		remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
	}

	do_action( 'give_after_cc_fields', $form_id, $args );

	$form = ob_get_clean();

	if ( false !== $echo ) {
		echo $form;
	}

	return $form;
}

add_action( 'give_stripe_cc_form', 'give_stripe_credit_card_form', 10, 3 );

/**
 * Add an errors div per form.
 *
 * @param int   $form_id Donation Form ID.
 * @param array $args    List of Donation Arguments.
 *
 * @access public
 * @since  2.5.0
 *
 * @return void
 */
function give_stripe_add_stripe_errors( $form_id, $args ) {
	echo '<div id="give-stripe-payment-errors-' . esc_html( $args['id_prefix'] ) . '"></div>';
}

add_action( 'give_donation_form_after_cc_form', 'give_stripe_add_stripe_errors', 8899, 2 );

/**
 * Add secret source field to apply the source generated on donation submit.
 *
 * @param int   $form_id Donation Form ID.
 * @param array $args    List of arguments.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_add_secret_payment_method_field( $form_id, $args ) {

	$id_prefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : 0;

	echo sprintf(
		'<input id="give-stripe-payment-method-%1$s" type="hidden" name="give_stripe_payment_method" value="">',
		esc_html( $id_prefix )
	);

}
add_action( 'give_donation_form_top', 'give_stripe_add_secret_payment_method_field', 10, 2 );
