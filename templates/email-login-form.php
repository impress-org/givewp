<?php
/**
 * Session Refresh Form
 *
 * This template is used to display an email form which will when submitted send an update donation receipt and also refresh the users session
 */

$show_form = true;
$email     = isset( $_POST['give_email'] ) ? $_POST['give_email'] : '';

//reCAPTCHA
$recaptcha_key    = give_get_option( 'recaptcha_key' );
$recaptcha_secret = give_get_option( 'recaptcha_secret' );
$enable_recaptcha = ( ! empty( $recaptcha_key ) && ! empty( $recaptcha_secret ) ) ? true : false;

// Form submission
if ( is_email( $email ) && wp_verify_nonce( $_POST['_wpnonce'], 'give' ) ) {

	// Use reCAPTCHA
	if ( $enable_recaptcha ) {

		$args = array(
			'secret'   => $recaptcha_secret,
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $_POST['give_ip']
		);

		if ( ! empty( $args['response'] ) ) {
			$request = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array( 'body' => $args ) );
			if ( ! is_wp_error( $request ) || 200 == wp_remote_retrieve_response_code( $request ) ) {

				$response = json_decode( $request['body'], true );

				// reCAPTCHA fail
				if ( ! $response['success'] ) {
					give_set_error( 'give_recaptcha_test_failed', apply_filters( 'give_recaptcha_test_failed_message', esc_html__( 'reCAPTCHA test failed.', 'give' ) ) );
				}

			} else {

				//Connection issue
				give_set_error( 'give_recaptcha_connection_issue', apply_filters( 'give_recaptcha_connection_issue_message', esc_html__( 'Unable to connect to reCAPTCHA server.', 'give' ) ) );

			}

		} // reCAPTCHA empty
		else {

			give_set_error( 'give_recaptcha_failed', apply_filters( 'give_recaptcha_failed_message', esc_html__( 'It looks like the reCAPTCHA test has failed.', 'give' ) ) );

		}
	}

	//If no errors or only expired token key error - then send email
	if ( ! give_get_errors() ) {

		$customer = Give()->customers->get_customer_by( 'email', $email );

		if ( isset( $customer->id ) ) {
			if ( Give()->email_access->can_send_email( $customer->id ) ) {
				Give()->email_access->send_email( $customer->id, $email );
				$show_form = false;
			}
		} else {
			give_set_error( 'give_no_donor_email_exists', apply_filters( 'give_no_donor_email_exists_message', esc_html__( 'It looks like that donor email address does not exist.', 'give' ) ) );
		}
	}
}

//Print any messages & errors
give_print_errors( 0 );

//Show the email login form?
if ( $show_form ) {
?>

	<div class="give-form">

		<?php
		if ( ! give_get_errors() ) {
			give_output_error( apply_filters( 'give_email_access_message', esc_html__( 'Please enter the email address you used for your donation. A verification email containing an access link will be sent to you.', 'give' ) ), true );
		} ?>

		<form method="post" action="" id="give-email-access-form">
			<label for="give-email"><?php esc_html__( 'Donation Email:', 'give' ); ?></label>
			<input id="give-email" type="email" name="give_email" value="" placeholder="<?php esc_attr_e( 'Your donation email', 'give' ); ?>"/>
			<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'give' ); ?>"/>

			<?php
			//Enable reCAPTCHA?
			if ( $enable_recaptcha ) { ?>

				<script>
					//IP verify for reCAPTCHA
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
			<?php } ?>

			<input type="submit" class="give-submit" value="<?php esc_attr_e( 'Email access token', 'give' ); ?>"/>
		</form>
	</div>

<?php
} else {

	give_output_error(
		sprintf(
			/* translators: %s: user email address */
			esc_html__( 'An email with an access link has been sent to %s.', 'give' ),
			$email
		),
		true,
		'success'
	);

}
?>