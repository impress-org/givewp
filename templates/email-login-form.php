<?php
/**
 * Session Refresh Form
 *
 * This template is used to display an email form which will when submitted send an update donation receipt and also refresh the users session
 */

global $give_access_form_outputted;
$show_form = true;
$email     = isset( $_POST['give_email'] ) ? $_POST['give_email'] : '';

// Declare Variables.
$recaptcha_key    = give_get_option( 'recaptcha_key' );
$recaptcha_secret = give_get_option( 'recaptcha_secret' );
$enable_recaptcha = ( ! empty( $recaptcha_key ) && ! empty( $recaptcha_secret ) ) ? true : false;
$access_token     = ! empty( $_GET['payment_key'] ) ? $_GET['payment_key'] : '';

// Only output the form once.
if ( $give_access_form_outputted ) {
	return;
}

// Form submission.
if ( is_email( $email ) && wp_verify_nonce( $_POST['_wpnonce'], 'give' ) ) {

	// Use reCAPTCHA
	if ( $enable_recaptcha ) {

		$args = array(
			'secret'   => $recaptcha_secret,
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $_POST['give_ip'],
		);

		if ( ! empty( $args['response'] ) ) {
			$request = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
				'body' => $args,
			) );
			if ( ! is_wp_error( $request ) || 200 == wp_remote_retrieve_response_code( $request ) ) {

				$response = json_decode( $request['body'], true );

				// reCAPTCHA fail.
				if ( ! $response['success'] ) {
					give_set_error( 'give_recaptcha_test_failed', apply_filters( 'give_recaptcha_test_failed_message', __( 'reCAPTCHA test failed.', 'give' ) ) );
				}
			} else {

				// Connection issue.
				give_set_error( 'give_recaptcha_connection_issue', apply_filters( 'give_recaptcha_connection_issue_message', __( 'Unable to connect to reCAPTCHA server.', 'give' ) ) );

			}
		} // End if().
		else {

			give_set_error( 'give_recaptcha_failed', apply_filters( 'give_recaptcha_failed_message', __( 'It looks like the reCAPTCHA test has failed.', 'give' ) ) );

		}
	}

	// If no errors or only expired token key error - then send email.
	if ( ! give_get_errors() ) {

		$donor = Give()->donors->get_donor_by( 'email', $email );
		$payment_ids = explode( ',', $donor->payment_ids );

		$payment_match = false;
		foreach( $payment_ids AS $payment_id ) {
			$payment = new Give_Payment( $payment_id );

			// Make sure Donation Access Token matches with donation details of donor whose email is provided.
			if ( $access_token === $payment->key ) {
				$payment_match = true;
			}

		}

		if ( ! $payment_match ) {
			give_set_error( 'give_email_access_token_not_match',  __( 'It looks like that email address provided and access token of the link does not match.', 'give' ) );

		} else {
			// Set Verification for Access.
			Give()->email_access->set_verify_key( $donor->id, $donor->email, $access_token );

			wp_safe_redirect( esc_url( get_permalink( give_get_option( 'history_page' ) ) . '?give_nl=' . $access_token ) );
		}

	}
} // End if().

// Print any messages & errors.
Give()->notices->render_frontend_notices( 0 );

// Show the email login form?
if ( $show_form ) { ?>
	<div class="give-form">

		<?php
		if ( ! give_get_errors() ) {
			Give()->notices->print_frontend_notice( apply_filters( 'give_email_access_message', __( 'Please enter the email address you used for your donation.', 'give' ) ), true );
		} ?>

		<form method="post" action="" id="give-email-access-form">
			<label for="give-email"><?php _e( 'Donation Email:', 'give' ); ?></label>
			<input id="give-email" type="email" name="give_email" value="" placeholder="<?php _e( 'Email Address', 'give' ); ?>" />
			<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'give' ); ?>" />

			<?php
			// Enable reCAPTCHA?
			if ( $enable_recaptcha ) { ?>

				<script>
					// IP verify for reCAPTCHA.
					(function( $ ) {
						$( function() {
							$.getJSON( 'https://api.ipify.org?format=jsonp&callback=?', function( json ) {
								$( '.give_ip' ).val( json.ip );
							} );
						} );
					})( jQuery );
				</script>

				<script src='https://www.google.com/recaptcha/api.js'></script>
				<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>"></div>
				<input type="hidden" name="give_ip" class="give_ip" value="" />
			<?php } ?>

			<input type="submit" class="give-submit" value="<?php _e( 'Verify Email', 'give' ); ?>" />
		</form>
	</div>

	<?php

}

// The form has been output.
$give_access_form_outputted = true;
?>
