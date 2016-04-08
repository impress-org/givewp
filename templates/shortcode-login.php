<?php
/**
 * This template is used to display the login form with [give_login]
 */
global $give_login_redirect;
if ( ! is_user_logged_in() ) {

	// Show any error messages after form submission
	give_print_errors( 0 ); ?>
	<form id="give-login-form" class="give-form" action="" method="post">
		<fieldset>
			<legend><?php _e( 'Log into Your Account', 'give' ); ?></legend>
			<?php do_action( 'give_login_fields_before' ); ?>
			<div class="give-login-username give-login">
				<label for="give_user_login"><?php _e( 'Username', 'give' ); ?></label>
				<input name="give_user_login" id="give_user_login" class="give-required give-input" type="text" title="<?php _e( 'Username', 'give' ); ?>" />
			</div>
			<div class="give-login-password give-login">
				<label for="give_user_pass"><?php _e( 'Password', 'give' ); ?></label>
				<input name="give_user_pass" id="give_user_pass" class="give-password give-required give-input" type="password" />
			</div>

			<div class="give-login-submit give-login">
				<input type="hidden" name="give_redirect" value="<?php echo esc_url( $give_login_redirect ); ?>" />
				<input type="hidden" name="give_login_nonce" value="<?php echo wp_create_nonce( 'give-login-nonce' ); ?>" />
				<input type="hidden" name="give_action" value="user_login" />
				<input id="give_login_submit" type="submit" class="give_submit" value="<?php _e( 'Log In', 'give' ); ?>" />
			</div>

			<div class="give-lost-password give-login">
				<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Lost Password', 'give' ); ?>"><?php _e( 'Lost Password?', 'give' ); ?></a>
			</div>
			<?php do_action( 'give_login_fields_after' ); ?>
		</fieldset>
	</form>
<?php } elseif( isset( $_GET['give-login-success'] ) && $_GET['give-login-success'] == true ) { ?>
	<?php give_output_error( apply_filters('give_successful_login_message', __( 'Login successful. Welcome!', 'give' )), true, 'success' ); ?>
<?php } else { ?>
	<?php give_output_error( apply_filters('give_already_logged_in_message', __( 'You are already logged in to the site.', 'give' )), true, 'warning' ); ?>
<?php } ?>