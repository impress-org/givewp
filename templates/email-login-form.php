<?php
/**
 * Session Refresh Form
 *
 * This template is used to display an email form which will when submitted send an update donation receipt and also refresh the users session
 */

$recaptcha_key    = give_get_option( 'recaptcha_key' );
$recaptcha_secret = give_get_option( 'recaptcha_secret' );
$enable_recaptcha = ( ! empty( $recaptcha_key ) && ! empty( $recaptcha_secret ) ) ? true : false;

// Print any messages & errors.
Give()->notices->render_frontend_notices( 0 );

?>
	<div class="give-form">
		<?php if ( isset( $_GET['give_action'] ) && 'view_receipt' === $_GET['give_action'] ) { ?>
			<h1><?php _e( 'Access Donation Receipt', 'give' ); ?></h1>
		<?php } ?>
		<form method="post" id="give-email-access-form">
			<p>
				<strong><?php echo apply_filters( 'give_email_access_message', __( 'Please enter the email address you used for your donation.', 'give' ) ); ?></strong>
			</p>
			<label for="give-email"><?php _e( 'Donation Email:', 'give' ); ?></label>
			<input id="give-email" type="email" name="give_email" value=""
			       placeholder="<?php _e( 'Email Address', 'give' ); ?>"/>
			<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'give' ); ?>"/>
			<?php
			// Enable reCAPTCHA?
			if ( $enable_recaptcha ) {
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
				<?php
			}
			?>
			<input type="submit" class="give-submit" value="<?php _e( 'Verify Email', 'give' ); ?>"/>
		</form>
	</div>
<?php

// The form has been output.
$give_access_form_outputted = true;
