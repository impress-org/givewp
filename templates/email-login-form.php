<?php
/**
 * Session Refresh Form
 *
 * This template is used to display an email form which will when submitted send an update donation receipt and also refresh the users session
 */

global $give_access_form_outputted;

$email            = isset( $_POST['give_email'] ) ? give_clean( $_POST['give_email'] ) : '';
$recaptcha_key    = give_get_option( 'recaptcha_key' );
$recaptcha_secret = give_get_option( 'recaptcha_secret' );
$enable_recaptcha = ( ! empty( $recaptcha_key ) && ! empty( $recaptcha_secret ) ) ? true : false;
$access_token     = ! empty( $_GET['payment_key'] ) ? $_GET['payment_key'] : '';

// Only output the form once.
if ( $give_access_form_outputted ) {
	return;
}

// Form submission.
if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'give' ) ) {

	if ( empty( $email ) ) {
		give_set_error( 'give_empty_email', __( 'Please enter the email address you used for your donation.', 'give' ) );
	}

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
		if ( ! $access_token && is_object( $donor ) ) {
			//Give()->session->set( 'receipt_access', $donation->key );
		} else if ( $access_token && is_object( $donor ) ) {

			// Scenario: Donation - Receipt Access.
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

			// Do required based on Payment Key and Access Token Match.
			if ( ! $donation_match ) {
				give_set_error( 'give_email_access_token_not_match', __( 'It looks like that email address provided and access token of the link does not match.', 'give' ) );
			} else {
				Give()->session->set( 'receipt_access', $access_token );
				wp_safe_redirect( esc_url( get_permalink( give_get_option( 'history_page' ) ) . '?payment_key=' . $access_token ) );
			}

		} else {
			give_set_error( 'give-no-donations', __( 'We are unable to fetch donations from the email you entered. Please try again.', 'give' ) );
		}
	}

} // End if().

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
