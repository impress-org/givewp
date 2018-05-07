<?php
/**
 * Session Refresh Form
 *
 * This template is used to display an email form which will when submitted send an update donation receipt and also
 * refresh the users session
 */

global $give_access_form_outputted;

// Only output the form once.
if ( $give_access_form_outputted ) {
	return;
}

$recaptcha_key    = give_get_option( 'recaptcha_key' );
$recaptcha_secret = give_get_option( 'recaptcha_secret' );

$enable_recaptcha = ( give_is_setting_enabled( give_get_option( 'enable_recaptcha' ) ) ) && ! empty( $recaptcha_key ) && ! empty( $recaptcha_secret ) ? true : false;

// Email already sent?
if ( isset( $_POST['email-access-sent'] ) ) {
	Give()->notices->print_frontend_notice( __( 'Please check your email and click on the link to access your complete donation history.', 'give' ), true, 'success' );

	return;
} elseif ( isset( $_POST['email-access-exhausted'] ) ) {

	// Too many emails sent?
	Give()->notices->print_frontend_notice(
		sprintf(
			__( 'Too many access email requests detected. Please wait %s before requesting a new donation history access link.', 'give' ),
			sprintf( _n( '%s minute', '%s minutes', Give()->email_access->verify_throttle / 60, 'give' ), Give()->email_access->verify_throttle / 60 )
		),
		true,
		'error'
	);

	return;
}


/**
 * Perform processing for email access form login.
 *
 * @since 1.8.17
 */
do_action( 'give_email_access_form_login' );

// Print any other messages & errors.
Give()->notices->render_frontend_notices();

?>
	<div class="give-form">
		<form method="post" id="give-email-access-form">
			<p><?php echo apply_filters( 'give_email_access_welcome_message', __( 'Please verify your email to access your donation history.', 'give' ) ); ?></p>

			<label for="give-email"><?php _e( 'Donation Email:', 'give' ); ?></label>
			<input id="give-email" type="email" name="give_email" value=""
				   placeholder="<?php _e( 'Email Address', 'give' ); ?>"/>
			<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'give' ); ?>"/>
			<input type="hidden" name="give_action" value="email_access_form_login"/>

			<?php
			// Enable reCAPTCHA?
			if ( $enable_recaptcha ) :
				?>
				<script>
					// IP verify for reCAPTCHA.
					(function ($) {
						$(function () {
							$.getJSON('https://api.ipify.org?format=jsonp&callback=?', function (json) {
								$('.give_ip').val(json.ip);
							});
						});
					})(jQuery);
				</script>

				<script src='https://www.google.com/recaptcha/api.js'></script>
				<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>"></div>
				<input type="hidden" name="give_ip" class="give_ip" value=""/>
			<?php endif; ?>


			<?php
			/**
			 * Add custom fields inside Email access form.
			 *
			 * @since 2.1.2
			 */
			do_action( 'give_email_access_form_login' );
			?>

			<input type="submit" class="give-submit" value="<?php _e( 'Verify Email', 'give' ); ?>"/>
		</form>
	</div>
<?php

// The form has been output.
$give_access_form_outputted = true;
