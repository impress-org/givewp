<?php
/**
 * This template is used to display the registration form with [give_register]
 */
Give()->notices->render_frontend_notices( 0 ); ?>

<form id="give-register-form" class="give-form" action="" method="post">
	<?php
	/**
	 * Fires in the registration shortcode, to the form top.
	 *
	 * Allows you to add elements to the form top.
	 *
	 * @since 1.0
	 */
	do_action( 'give_register_form_fields_top' );
	?>

	<fieldset>
		<legend><?php esc_html_e( 'Register a New Account', 'give' ); ?></legend>

		<?php
		/**
		 * Fires in the registration shortcode, before the registration fields.
		 *
		 * Allows you to add elements to the fieldset, before the fields.
		 *
		 * @since 1.0
		 */
		do_action( 'give_register_form_fields_before' );
		?>

		<div class="form-row form-row-first form-row-responsive">
			<label for="give-user-login"><?php esc_html_e( 'Username', 'give' ); ?></label>
			<input id="give-user-login" class="required give-input" type="text" name="give_user_login" required aria-required="true" />
		</div>

		<div class="form-row form-row-last form-row-responsive">
			<label for="give-user-email"><?php esc_html_e( 'Email', 'give' ); ?></label>
			<input id="give-user-email" class="required give-input" type="email" name="give_user_email" required aria-required="true" />
		</div>

		<div class="form-row form-row-first form-row-responsive">
			<label for="give-user-pass"><?php esc_html_e( 'Password', 'give' ); ?></label>
			<input id="give-user-pass" class="password required give-input" type="password" name="give_user_pass" required aria-required="true" />
		</div>

		<div class="form-row form-row-last form-row-responsive">
			<label for="give-user-pass2"><?php esc_html_e( 'Confirm PW', 'give' ); ?></label>
			<input id="give-user-pass2" class="password required give-input" type="password" name="give_user_pass2" required aria-required="true" />
		</div>

		<?php
		/**
		 * Fires in the registration shortcode, before submit button.
		 *
		 * Allows you to add elements before submit button.
		 *
		 * @since 1.0
		 */
		do_action( 'give_register_form_fields_before_submit' );
		?>

		<div class="give-hidden">
			<input type="hidden" name="give_honeypot" value="" />
			<input type="hidden" name="give_action" value="user_register" />
			<input type="hidden" name="give_redirect" value="<?php echo esc_url( $give_register_redirect ); ?>" />
		</div>

		<div class="form-row">
			<input class="button" name="give_register_submit" type="submit" value="<?php esc_attr_e( 'Register', 'give' ); ?>" />
		</div>

		<?php
		/**
		 * Fires in the registration shortcode, after the registration fields.
		 *
		 * Allows you to add elements to the fieldset, after the fields and the submit button.
		 *
		 * @since 1.0
		 */
		do_action( 'give_register_form_fields_after' );
		?>

	</fieldset>

	<?php
	/**
	 * Fires in the registration shortcode, to the form bottom.
	 *
	 * Allows you to add elements to the form bottom.
	 *
	 * @since 1.0
	 */
	do_action( 'give_register_form_fields_bottom' );
	?>
</form>
