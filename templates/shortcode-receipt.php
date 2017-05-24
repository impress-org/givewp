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
$status       = $payment->post_status;
$status_label = give_get_payment_status( $payment, true );

// Show payment status notice based on shortcode attribute.
if ( filter_var( $give_receipt_args['status_notice'], FILTER_VALIDATE_BOOLEAN ) ) {
	$notice_message = '';
	$notice_type    = 'warning';

	switch ( $status ) {
		case 'publish':
			$notice_message = esc_html__( 'Payment Complete: Thank you for your donation.', 'give' );
			$notice_type    = 'success';
			break;
		case 'pending':
			$notice_message = esc_html__( 'Payment Pending: Your donation is currently processing..', 'give' );
			$notice_type    = 'warning';
			break;
		case 'refunded':
			$notice_message = esc_html__( 'Payment Refunded: Your donation has been refunded.', 'give' );
			$notice_type    = 'warning';
			break;
		case 'preapproval':
			$notice_message = esc_html__( 'Payment Preapproved: Thank you for your donation.', 'give' );
			$notice_type    = 'warning';
			break;
		case 'failed':
			$notice_message = esc_html__( 'Payment Failed: Please contact the site owner for assistance.', 'give' );
			$notice_type    = 'error';
			break;
		case 'cancelled':
			$notice_message = esc_html__( 'Payment Cancelled: Your donation has been cancelled.', 'give' );
			$notice_type    = 'error';
			break;
		case 'abandoned':
			$notice_message = esc_html__( 'Payment Abandoned: This donation has not been completed.', 'give' );
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

		<?php foreach( $give_receipt_args['donation_receipt'] as $receipt_item ){ ?>
            <?php if ( filter_var( $receipt_item['display'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
            <tr>
                <td scope="row"><strong><?php echo $receipt_item['name']; ?></strong></td>
                <td><?php echo $receipt_item['value']; ?></td>
            </tr>
            <?php endif; ?>
        <?php } ?>

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
