<?php
/**
 * This template is used to display an email form which will when submitted send an update donation receipt and also refresh the users session
 */

//Send functionality
$show_form = true;
$email     = isset( $_POST['give_email'] ) ? $_POST['give_email'] : '';

//Verify nonce for security
if ( is_email( $email ) && wp_verify_nonce( $_POST['_wpnonce'], 'give' ) ) {
	$customer_id = give_get_customer_id_from_email( $email );

	if ( $customer_id ) {
		if ( give_can_send_refresh_email( $customer_id ) ) {

			$verify_key = wp_generate_password( 20, false );
			// Generate a new verify key
			$this->set_verify_key( $customer_id, $email, $verify_key );

			Give()->emails->send( $customer_id, $email );
			$show_form = false;
		}
	} else {
		give_set_error('no_customer_email_found', __( 'That donation email does not exist', 'give' ));
	}
}
?>

<?php if ( ! empty( Give()->error ) ) : ?>
	<?php give_print_errors(0); ?>
<?php endif; ?>

<?php if ( $show_form ) : ?>

	<div class="give-refresh-session-form-wrap">
		<form method="post" action="" class="give-refresh-session-form">
			<input type="email" name="give_email" value="" placeholder="<?php _e( 'Your donation email', 'give' ); ?>"/>
			<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'give' ); ?>"/>
			<input type="submit" class="give-submit" value="<?php _e( 'Email access token', 'give' ); ?>"/>
		</form>
	</div>

<?php else : ?>

		<?php give_output_error(__( 'An access token has been emailed to you.', 'give' ), true, 'success'); ?>

<?php endif; ?>