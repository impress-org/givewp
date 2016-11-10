<?php
/**
 * This template is used to display the login form with [give_login]
 */
global $give_login_redirect, $give_logout_redirect;
if ( ! is_user_logged_in() ) {

	// Show any error messages after form submission
	give_print_errors( 0 ); ?>
	<form id="give-login-form" class="give-form" action="" method="post">
		<fieldset>
			<legend><?php esc_html_e( 'Log into Your Account', 'give' ); ?></legend>
			<?php
			/**
			 * Fires in the login shortcode, before the login fields.
			 *
			 * Allows you to add new fields before the default fields.
			 *
			 * @since 1.0
			 */
			do_action( 'give_login_fields_before' );
			?>
			<div class="give-login-username give-login">
				<label for="give_user_login"><?php esc_html_e( 'Username', 'give' ); ?></label>
				<input name="give_user_login" id="give_user_login" class="give-required give-input" type="text" required aria-required="true" />
			</div>

			<div class="give-login-password give-login">
				<label for="give_user_pass"><?php esc_html_e( 'Password', 'give' ); ?></label>
				<input name="give_user_pass" id="give_user_pass" class="give-password give-required give-input" type="password" required aria-required="true" />
			</div>

			<div class="give-login-submit give-login">
				<input type="hidden" name="give_login_redirect" value="<?php echo esc_url( $give_login_redirect ); ?>" />
				<input type="hidden" name="give_login_nonce" value="<?php echo wp_create_nonce( 'give-login-nonce' ); ?>" />
				<input type="hidden" name="give_action" value="user_login" />
				<input id="give_login_submit" type="submit" class="give_submit" value="<?php esc_attr_e( 'Log In', 'give' ); ?>" />
			</div>

			<div class="give-lost-password give-login">
				<a href="<?php echo wp_lostpassword_url(); ?>"><?php esc_html_e( 'Reset Password', 'give' ); ?></a>
			</div>
			<?php
			/**
			 * Fires in the login shortcode, after the login fields.
			 *
			 * Allows you to add new fields after the default fields.
			 *
			 * @since 1.0
			 */
			do_action( 'give_login_fields_after' );
			?>
		</fieldset>
	</form>
<?php } elseif( isset( $_GET['give-login-success'] ) && $_GET['give-login-success'] == true ) { ?>
	<?php give_output_error( apply_filters('give_successful_login_message', esc_html__( 'Login successful. Welcome!', 'give' )), true, 'success' ); ?>
<?php } else { ?>
	<?php give_output_error( apply_filters('give_already_logged_in_message', sprintf( __( 'You are already logged in to the site. <a href="%s">Click here</a> to logout.', 'give' ), esc_url( $give_logout_redirect ) ) ), true, 'warning' ); ?>
<?php } ?>