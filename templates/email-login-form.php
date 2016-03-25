<?php
/**
 * Session Refresh Form
 *
 * @description: This template is used to display an email form which will when submitted send an update donation receipt and also refresh the users session
 */

$show_form = true;
$email     = isset( $_POST['give_email'] ) ? $_POST['give_email'] : '';

// Form submission
if ( is_email( $email ) && wp_verify_nonce( $_POST['_wpnonce'], 'give' ) ) {

	// Use reCAPTCHA
	if ( defined( 'RECAPTCHA_KEY' ) ) {

		$args = array(
			'secret'   => RECAPTCHA_SECRET,
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $_POST['give_ip']
		);

		if ( ! empty( $args['response'] ) ) {
			$request = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array( 'body' => $args ) );
			if ( ! is_wp_error( $request ) || 200 == wp_remote_retrieve_response_code( $request ) ) {

				$response = json_decode( $request['body'], true );

				// reCAPTCHA fail
				if ( ! $response['success'] ) {
					give_set_error( 'give_recaptcha_test_failed', apply_filters( 'give_recaptcha_test_failed_message', __( 'reCAPTCHA test failed', 'give' ) ) );
				}

			} else {

				give_set_error( 'give_recaptcha_connection_issue', apply_filters( 'give_recaptcha_connection_issue_message', __( 'Unable to connect to reCAPTCHA server', 'give' ) ) );

			}

		} // reCAPTCHA empty
		else {

			give_set_error( 'give_recaptcha_failed', apply_filters( 'give_recaptcha_failed_message', __( 'Sorry, it looks like the reCAPTCHA test has failed', 'give' ) ) );

		}
	}

	if ( ! give_get_errors() ) {

		$customer = Give()->customers->get_customer_by( 'email', $email );

		if ( isset( $customer->id ) ) {
			if ( Give()->email_login->can_send_email( $customer->id ) ) {
				Give()->email_login->send_email( $customer->id, $email );
				$show_form = false;
			}
		} else {
			give_set_error( 'give_no_donor_email_exists', apply_filters( 'give_no_donor_email_exists_message', __( 'Sorry, it looks like that donor email address does not exist', 'give' ) ) );
		}
	}
}

?>
	<script>
		(function ($) {
			$(function () {
				$.getJSON('https://api.ipify.org?format=jsonp&callback=?', function (json) {
					$('.give_ip').val(json.ip);
				});
			});
		})(jQuery);
	</script>

	<h3><?php _e( 'Access Your Account', 'give' ); ?></h3>

<?php give_print_errors( 0 ); ?>

<?php
//Show the email login form?
if ( $show_form ) { ?>

	<script src='https://www.google.com/recaptcha/api.js'></script>
	<div class="give-form">
		<form method="post" action="">
			<input type="email" name="give_email" value="" placeholder="<?php _e( 'Your donation email', 'give' ); ?>"/>
			<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'give' ); ?>"/>

			<?php if ( defined( 'RECAPTCHA_KEY' ) ) : ?>
				<div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_KEY; ?>"></div>
				<input type="hidden" name="give_ip" class="give_ip" value=""/>
			<?php endif; ?>

			<input type="submit" class="give-submit" value="<?php _e( 'Email access token', 'give' ); ?>"/>
		</form>
	</div>

<?php } else { ?>

	<?php give_output_error( sprintf( __( 'An email with an access link has been sent to %1$s.', 'give' ), $email ), true, 'success' ); ?>

<?php } ?>