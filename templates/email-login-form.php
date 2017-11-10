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

	// Use reCAPTCHA.
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

		$donation_ids   = array();
		$donation_match = false;
		$donor          = Give()->donors->get_donor_by( 'email', $email );

		// Verify that donor object is present and donor is connected with its user profile or not.
		if ( ! $access_token && is_object( $donor) && 0 === (int) $donor->user_id ) {
			give_set_error( 'give_email_access_donor_only',__( 'To access complete donation history, please click the <strong>View it in browser</strong> link in your Donation Receipt Email', 'give' ) );
		} else {

			if ( ! empty( $donor->payment_ids ) ) {
				$donation_ids = explode( ',', $donor->payment_ids );
			}

			foreach ( $donation_ids as $donation_id ) {
				$donation = new Give_Payment( $donation_id );

				// Make sure Donation Access Token matches with donation details of donor whose email is provided.
				if ( $access_token === $donation->key ) {
					$donation_match = true;
				}

			}
			$donation_match = true;

//			if( ( empty( $access_token ) && ! $donation_match ) || $donation_match ) {
//				// Set Verification for Access.
//				$verify_key = wp_generate_password( 20, false );
//				Give()->email_access->set_verify_key( $donor->id, $donor->email, $verify_key );
//				wp_redirect( esc_url( add_query_arg( array(
//					'give_nl' => $verify_key,
//				), get_permalink( give_get_option( 'history_page' ) ) ) ) );
//			} else {
//				give_set_error( 'give_email_access_token_not_match', __( 'It looks like that email address provided and access token of the link does not match.', 'give' ) );
//			}
			//var_dump($donor);

			if ( ! $donation_match ) {
				give_set_error( 'give_email_access_token_not_match', __( 'It looks like that email address provided and access token of the link does not match.', 'give' ) );
			} else {
				Give()->email_access->set_verify_key( $donor->id, $donor->email, $access_token );
				wp_safe_redirect( esc_url( get_permalink( give_get_option( 'history_page' ) ) . '?give_nl=' . $access_token ) );
			}
		}
		//give_die();
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

		<form method="post" id="give-email-access-form">
			<label for="give-email"><?php _e( 'Donation Email:', 'give' ); ?></label>
			<input id="give-email" type="email" name="give_email" value="" placeholder="<?php _e( 'Email Address', 'give' ); ?>" />
			<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'give' ); ?>" />
			<input type="hidden" name="give_nl" value="<?php echo wp_generate_password( 20, false ); ?>" />

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

	// The form has been output.
	$give_access_form_outputted = true;
}