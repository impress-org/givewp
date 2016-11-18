<?php
/**
 * This template is used to display the donation summary with [give_receipt]
 */
global $give_receipt_args, $payment;

//Validation: Ensure $payment var is set
if ( empty( $payment ) && isset( $give_receipt_args['id'] ) ) {
	$payment = get_post( $give_receipt_args['id'] );
}

//Double-Validation: Check for $payment global
if ( empty( $payment ) ) {
	give_output_error( esc_html__( 'The specified receipt ID appears to be invalid.', 'give' ) );

	return;
}

$id           = $payment->ID;
$meta         = give_get_payment_meta( $payment->ID );
$donation     = give_get_payment_form_title( $meta );
$user         = give_get_payment_meta_user_info( $payment->ID );
$email        = give_get_payment_user_email( $payment->ID );
$status       = $payment->post_status;
$status_label = give_get_payment_status( $payment, true );

// Show payment status notice based on shortcode attribute.
if ( true === $give_receipt_args['status_notice'] ) {
	$notice_message = '';
	$notice_type    = 'warning';

	switch ( $status ) {
		case 'publish':
			$notice_message = esc_html__( 'Payment Complete: Thank you for your donation.', 'give' );
			$notice_type    = 'success';
			break;
		case 'pending':
			$notice_message = esc_html__( 'Payment Pending: Please contact the site owner for assistance.', 'give' );
			$notice_type    = 'warning';
			break;
		case 'refunded':
			$notice_message = esc_html__( 'Payment Refunded: Please contact the site owner for assistance.', 'give' );
			$notice_type    = 'warning';
			break;
		case 'preapproval':
			$notice_message = esc_html__( 'Payment Preapproved: Please contact the site owner for assistance.', 'give' );
			$notice_type    = 'warning';
			break;
		case 'failed':
			$notice_message = esc_html__( 'Payment Failed: Please contact the site owner for assistance.', 'give' );
			$notice_type    = 'error';
			break;
		case 'cancelled':
			$notice_message = esc_html__( 'Payment Cancelled: Please contact the site owner for assistance.', 'give' );
			$notice_type    = 'error';
			break;
		case 'abandoned':
			$notice_message = esc_html__( 'Payment Abandoned: Please contact the site owner for assistance.', 'give' );
			$notice_type    = 'error';
			break;
		case 'revoked':
			$notice_message = esc_html__( 'Payment Revoked: Please contact the site owner for assistance.', 'give' );
			$notice_type    = 'error';
			break;
	}

	if ( ! empty( $notice_message ) ) {
		/**
		 * Filters payment status notice for receipts.
		 *
		 * By default, a success, warning, or error notice appears on the receipt
		 * with payment status. This filter allows the HTML markup
		 * and messaging for that notice to be customized.
		 *
		 * @since 1.0
		 *
		 * @param string $notice HTML markup for the default notice.
		 * @param int    $id     Post ID where the notice is displayed.
		 * @param string $status Payment status.
		 * @param array  $meta   Array of meta data related to the payment.
		 */
		echo apply_filters( 'give_receipt_status_notice', give_output_error( $notice_message, false, $notice_type ), $id, $status, $meta );
	}
}

/**
 * Fires in the payment receipt shortcode, before the receipt main table.
 *
 * Allows you to add elements before the table.
 *
 * @since 1.0
 *
 * @param object $payment           The payment object.
 * @param array  $give_receipt_args Receipt_argument.
 */
do_action( 'give_payment_receipt_before_table', $payment, $give_receipt_args );
?>

	<table id="give_donation_receipt" class="give-table">
		<thead>
		<?php
		/**
		 * Fires in the payment receipt shortcode, before the receipt first header item.
		 *
		 * Allows you to add new <th> elements before the receipt first header item.
		 *
		 * @since 1.7
		 *
		 * @param object $payment           The payment object.
		 * @param array  $give_receipt_args Receipt_argument.
		 */
		do_action( 'give_payment_receipt_header_before', $payment, $give_receipt_args );
		?>
		<tr>
			<th scope="colgroup" colspan="2">
				<span class="give-receipt-thead-text"><?php esc_html_e( 'Donation Receipt', 'give' ) ?></span>
			</th>
		</tr>
		<?php
		/**
		 * Fires in the payment receipt shortcode, after the receipt last header item.
		 *
		 * Allows you to add new <th> elements after the receipt last header item.
		 *
		 * @since 1.7
		 *
		 * @param object $payment           The payment object.
		 * @param array  $give_receipt_args Receipt_argument.
		 */
		do_action( 'give_payment_receipt_header_after', $payment, $give_receipt_args );
		?>
		</thead>

		<tbody>
		<?php
		/**
		 * Fires in the payment receipt shortcode, before the receipt first item.
		 *
		 * Allows you to add new <td> elements before the receipt first item.
		 *
		 * @since 1.7
		 *
		 * @param object $payment           The payment object.
		 * @param array  $give_receipt_args Receipt_argument.
		 */
		do_action( 'give_payment_receipt_before', $payment, $give_receipt_args );
		?>

		<?php if ( filter_var( $give_receipt_args['donor'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Donor:', 'give' ); ?></strong></td>
				<td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $give_receipt_args['date'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Date:', 'give' ); ?></strong></td>
				<td><?php echo date_i18n( give_date_format(), strtotime( $meta['date'] ) ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $give_receipt_args['price'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Total Donation:', 'give' ); ?></strong></td>
				<td><?php echo give_payment_amount( $payment->ID ); ?></td>
			</tr>
		<?php endif; ?>

		<tr>
			<td scope="row" class="give_receipt_payment_status">
				<strong><?php esc_html_e( 'Donation:', 'give' ); ?></strong></td>
			<td class="give_receipt_payment_status"><?php echo $donation; ?></td>
		</tr>

		<?php if ( filter_var( $give_receipt_args['payment_status'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td scope="row" class="give_receipt_payment_status">
					<strong><?php esc_html_e( 'Donation Status:', 'give' ); ?></strong></td>
				<td class="give_receipt_payment_status <?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status_label ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $give_receipt_args['payment_id'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Donation ID:', 'give' ); ?></strong></td>
				<td><?php echo give_get_payment_number( $payment->ID ); ?></td>
			</tr>
		<?php else : ?>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Payment:', 'give' ); ?></strong></td>
				<td><?php esc_html_e( 'Details:', 'give' ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $give_receipt_args['payment_key'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Payment Key:', 'give' ); ?></strong></td>
				<td><?php echo get_post_meta( $payment->ID, '_give_payment_purchase_key', true ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $give_receipt_args['payment_method'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Payment Method:', 'give' ); ?></strong></td>
				<td><?php echo give_get_gateway_checkout_label( give_get_payment_gateway( $payment->ID ) ); ?></td>
			</tr>
		<?php endif; ?>

		<?php
		//No fees built in just yet...
		//@TODO: Fees
		if ( ( $fees = give_get_payment_fees( $payment->ID, 'fee' ) ) ) : ?>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Fees:', 'give' ); ?></strong></td>
				<td>
					<ul class="give_receipt_fees">
						<?php foreach ( $fees as $fee ) : ?>
							<li>
								<span class="give_fee_label"><?php echo esc_html( $fee['label'] ); ?></span>
								<span class="give_fee_sep">&nbsp;&ndash;&nbsp;</span>
								<span
									class="give_fee_amount"><?php echo give_currency_filter( give_format_amount( $fee['amount'] ) ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</td>
			</tr>
		<?php endif; ?>

		<?php
		/**
		 * Fires in the payment receipt shortcode, after the receipt last item.
		 *
		 * Allows you to add new <td> elements after the receipt last item.
		 *
		 * @since 1.7
		 *
		 * @param object $payment           The payment object.
		 * @param array  $give_receipt_args Receipt_argument.
		 */
		do_action( 'give_payment_receipt_after', $payment, $give_receipt_args );
		?>
		</tbody>
	</table>

<?php
/**
 * Fires in the payment receipt shortcode, after the receipt main table.
 *
 * Allows you to add elements after the table.
 *
 * @since 1.7
 *
 * @param object $payment           The payment object.
 * @param array  $give_receipt_args Receipt_argument.
 */
do_action( 'give_payment_receipt_after_table', $payment, $give_receipt_args );
?>