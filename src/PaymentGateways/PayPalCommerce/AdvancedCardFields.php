<?php

namespace Give\PaymentGateways\PayPalCommerce;

class AdvancedCardFields {
	/**
	 * Bootstrap class
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		add_action( 'give_paypal-commerce_cc_form', [ $this, 'addCreditCardForm' ], 10, 3 );

		return $this;
	}

	/**
	 * Stripe uses it's own credit card form because the card details are tokenized.
	 *
	 * We don't want the name attributes to be present on the fields in order to
	 * prevent them from getting posted to the server.
	 *
	 * @param  int  $form_id  Donation Form ID.
	 * @param  int  $args  Donation Form Arguments.
	 * @param  bool  $echo  Status to display or not.
	 *
	 * @access public
	 * @return string $form
	 * @since  1.0
	 *
	 */
	public function addCreditCardForm( $form_id, $args, $echo = true ) {
		ob_start();
		$id_prefix              = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';
		$stripe_cc_field_format = give_get_option( 'stripe_cc_fields_format', 'multi' );

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

			?>
			<div id="give-card-number-wrap" class="form-row form-row-two-thirds form-row-responsive give-paypal-commerce-cc-field-wrap">
				<div>
					<label for="give-card-number-field-<?php echo esc_html( $id_prefix ); ?>" class="give-label">
						<?php esc_attr_e( 'Card Number', 'give' ); ?>
						<span class="give-required-indicator">*</span>
														  data-tooltip="
														  <?php
															esc_attr_e(
																'The (typically) 16 digits on the front of your credit card.',
																'give'
															);
															?>
								  "></span>
						<span class="card-type"></span>
					</label>
					<div id="give-card-number-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty give-paypal-commerce-cc-field give-paypal-commerce-card-number-field"></div>
				</div>
			</div>

			<div id="give-card-cvc-wrap" class="form-row form-row-one-third form-row-responsive give-paypal-commerce-cc-field-wrap">
				<div>
					<label for="give-card-cvc-field-<?php echo esc_html( $id_prefix ); ?>" class="give-label">
						<?php esc_attr_e( 'CVC', 'give' ); ?>
						<span class="give-required-indicator">*</span>
														  data-tooltip="
														  <?php
															esc_attr_e(
																'The 3 digit (back) or 4 digit (front) value on your card.',
																'give'
															);
															?>
								  "></span>
					</label>
					<div id="give-card-cvc-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty give-paypal-commerce-cc-field give-paypal-commerce-card-cvc-field"></div>
				</div>
			</div>

			<div id="give-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive">
				<label for="card_name" class="give-label">
					<?php esc_attr_e( 'Cardholder Name', 'give' ); ?>
					<span class="give-required-indicator">*</span>
												  data-tooltip="
												  <?php
													esc_attr_e(
														'The name of the credit card account holder.',
														'give'
													);
													?>
							  "></span>
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

			<div id="give-card-expiration-wrap" class="card-expiration form-row form-row-one-third form-row-responsive give-paypal-commerce-cc-field-wrap">
				<div>
					<label for="give-card-expiration-field-<?php echo esc_html( $id_prefix ); ?>" class="give-label">
						<?php esc_attr_e( 'Expiration', 'give' ); ?>
						<span class="give-required-indicator">*</span>
														  data-tooltip="
														  <?php
															esc_attr_e(
																'The date your credit card expires, typically on the front of the card.',
																'give'
															);
															?>
								  "></span>
					</label>

					<div id="give-card-expiration-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty give-paypal-commerce-cc-field give-paypal-commerce-card-expiration-field"></div>
				</div>
			</div>
			<?php

			/**
			 * This action hook is used to display content after the Credit Card expiration field.
			 *
			 * Note: Kept this hook as it is.
			 *
			 * @param  int  $form_id  Donation Form ID.
			 * @param  array  $args  List of additional arguments.
			 *
			 * @since 2.5.0
			 *
			 */
			do_action( 'give_after_cc_expiration', $form_id, $args );

			/**
			 * This action hook is used to display content after the Credit Card expiration field.
			 *
			 * @param  int  $form_id  Donation Form ID.
			 * @param  array  $args  List of additional arguments.
			 *
			 * @since 2.5.0
			 *
			 */
			do_action( 'give_stripe_after_cc_expiration', $form_id, $args );
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
}
