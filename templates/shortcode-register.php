<?php
/**
 * This template is used to display the registration form with [give_register]
 */
global $give_register_redirect;

give_print_errors( 0 ); ?>

<form id="give-register-form" class="give-form" action="" method="post">
	<?php do_action( 'give_register_form_fields_top' ); ?>

	<fieldset>
		<legend><?php _e( 'Register a New Account', 'give' ); ?></legend>

		<?php do_action( 'give_register_form_fields_before' ); ?>

		<div class="form-row form-row-first">
			<label for="give-user-login"><?php _e( 'Username', 'give' ); ?></label>
			<input id="give-user-login" class="required give-input" type="text" name="give_user_login" title="<?php esc_attr_e( 'Username', 'give' ); ?>" />
		</div>

		<div class="form-row form-row-last">
			<label for="give-user-email"><?php _e( 'Email', 'give' ); ?></label>
			<input id="give-user-email" class="required give-input" type="email" name="give_user_email" title="<?php esc_attr_e( 'Email Address', 'give' ); ?>" />
		</div>

		<div class="form-row form-row-first">
			<label for="give-user-pass"><?php _e( 'Password', 'give' ); ?></label>
			<input id="give-user-pass" class="password required give-input" type="password" name="give_user_pass" />
		</div>

		<div class="form-row form-row-last">
			<label for="give-user-pass2"><?php _e( 'Confirm PW', 'give' ); ?></label>
			<input id="give-user-pass2" class="password required give-input" type="password" name="give_user_pass2" />
		</div>


		<?php do_action( 'give_register_form_fields_before_submit' ); ?>

		<div class="give-hidden">
			<input type="hidden" name="give_honeypot" value="" />
			<input type="hidden" name="give_action" value="user_register" />
			<input type="hidden" name="give_redirect" value="<?php echo esc_url( $give_register_redirect ); ?>" />
		</div>

		<div class="form-row">
			<input class="button" name="give_register_submit" type="submit" value="<?php esc_attr_e( 'Register', 'give' ); ?>" />
		</div>

		<?php do_action( 'give_register_form_fields_after' ); ?>

	</fieldset>

	<?php do_action( 'give_register_form_fields_bottom' ); ?>
</form>
