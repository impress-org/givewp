<?php
/**
 * This template is used to display the donation summary with [give_receipt]
 */

global $give_receipt_args, $donation;

// Validation: Ensure $donation var is set.
if ( empty( $donation ) ) {
	$donation = ! empty( $give_receipt_args['id'] ) ? get_post( $give_receipt_args['id'] ) : 0;
}

// Double-Validation: Check for $donation global.
if ( empty( $donation ) ) {
	Give_Notices::print_frontend_notice( __( 'The specified receipt ID appears to be invalid.', 'give' ) );
	return;
}

$donation_id     = $donation->ID;
$donation_number = Give()->seq_donation_number->get_serial_code( $donation_id );
$form_id         = give_get_payment_meta( $donation_id, '_give_payment_form_id', true );
$form_name       = give_get_donation_form_title( $donation_id );
$user            = give_get_payment_meta_user_info( $donation_id );
$email           = give_get_payment_user_email( $donation_id );
$status          = $donation->post_status;
$status_label    = give_get_payment_status( $donation_id, true );
$company_name    = give_get_payment_meta( $donation_id, '_give_donation_company', true );

// Update donor name, if title prefix is set.
$full_name = give_get_donor_name_with_title_prefixes( $user['title'], "{$user['first_name']} {$user['last_name']}" );

/**
 * Generate Donation Receipt Arguments.
 *
 * Added donation receipt array to global variable $give_receipt_args to
 * manage it from single variable
 *
 * @since 1.8.8
 */
$give_receipt_args['donation_receipt']['donor'] = array(
	'name'    => __( 'Donor', 'give' ),
	'value'   => $full_name,
	'display' => $give_receipt_args['donor'],
);

/**
 * Show Company name on Donation receipt Page
 *
 * @since 2.0.7
 *
 * @param bool show/hide company name in donation receipt page.
 *
 * @return bool show/hide company name in donation receipt page.
 */
$give_receipt_args['donation_receipt']['company_name'] = array(
	'name'    => __( 'Company Name', 'give' ),
	'value'   => esc_attr( $company_name ),
	// Do not show company field if empty
	'display' => empty( $company_name ) ? false : $give_receipt_args['company_name'],
);

$give_receipt_args['donation_receipt']['date'] = array(
	'name'    => __( 'Date', 'give' ),
	'value'   => date_i18n( give_date_format(), strtotime( give_get_payment_completed_date( $donation_id ) ) ),
	'display' => $give_receipt_args['date'],
);

$give_receipt_args['donation_receipt']['total_donation'] = array(
	'name'    => __( 'Total Donation', 'give' ),
	'value'   => give_donation_amount(
		$donation_id,
		array(
			'currency' => true,
			'amount'   => true,
			'type'     => 'receipt',
		)
	),
	'display' => $give_receipt_args['price'],
);

$give_receipt_args['donation_receipt']['donation'] = array(
	'name'    => __( 'Donation', 'give' ),
	'value'   => $form_name,
	'display' => true,
);

$give_receipt_args['donation_receipt']['donation_status'] = array(
	'name'    => __( 'Donation Status', 'give' ),
	'value'   => esc_attr( $status_label ),
	'display' => $give_receipt_args['payment_status'],
);

$give_receipt_args['donation_receipt']['donation_id'] = array(
	'name'    => __( 'Donation ID', 'give' ),
	'value'   => $donation_number,
	'display' => $give_receipt_args['payment_id'],
);

$give_receipt_args['donation_receipt']['payment_method'] = array(
	'name'    => __( 'Payment Method', 'give' ),
	'value'   => give_get_gateway_checkout_label( give_get_payment_gateway( $donation_id ) ),
	'display' => $give_receipt_args['payment_method'],
);

/**
 * Extend Give Donation Receipt
 *
 * You can easily extend the donation receipt argument using the filter give_donation_receipt_args
 *
 * @params array $give_receipt_args['donation_receipt'] Array of arguments for Donation Receipt.
 * @params int   $donation_id                           Donation ID.
 * @params int   $form_id                               Donation Form ID.
 *
 * @since 1.8.8
 */
$give_receipt_args['donation_receipt'] = apply_filters( 'give_donation_receipt_args', $give_receipt_args['donation_receipt'], $donation_id, $form_id );

// When the donation were made through offline donation, We won't show receipt and payment status though.
if ( 'offline' === give_get_payment_gateway( $donation_id ) && 'pending' === $status ) {

	/**
	 * Before the offline donation receipt content starts.
	 *
	 * @since 1.8.14
	 *
	 * @param Give_Payment $donation          Donation object.
	 * @param array        $give_receipt_args Receipt Arguments.
	 */
	do_action( 'give_receipt_before_offline_payment', $donation, $give_receipt_args );
	?>
	<h2><?php echo apply_filters( 'give_receipt_offline_payment_heading', __( 'Your Donation is Almost Complete!', 'give' ) ); ?></h2>
	<div id="give_donation_receipt" class="<?php echo esc_attr( apply_filters( 'give_receipt_offline_payment_classes', 'give_receipt_offline_payment' ) ); ?>">
		<?php
		// Instruction for offline donation.
		$offline_instruction = give_get_offline_payment_instruction( $form_id, true );

		/**
		 * Instruction for the offline donation.
		 *
		 * @since 1.8.14
		 *
		 * @param string       $offline_instruction Offline instruction content.
		 * @param Give_Payment $donation            Donation object.
		 * @param integer      $form_id             Donation form id.
		 */
		echo apply_filters( 'give_receipt_offline_payment_instruction', $offline_instruction, $donation, $form_id );
		?>
	</div>
	<?php
	/**
	 * After the offline donation content ends.
	 *
	 * @since 1.8.14
	 *
	 * @param Give_Payment $donation          Donation object.
	 * @param array        $give_receipt_args Receipt Arguments.
	 */
	do_action( 'give_receipt_after_offline_payment', $donation, $give_receipt_args );

	return;
}

// Show payment status notice based on shortcode attribute.
if ( filter_var( $give_receipt_args['status_notice'], FILTER_VALIDATE_BOOLEAN ) ) {
	$notice_message = '';
	$notice_type    = 'warning';

	switch ( $status ) {
		case 'publish':
			$notice_message = __( 'Payment Complete: Thank you for your donation.', 'give' );
			$notice_type    = 'success';
			break;
		case 'pending':
			$notice_message = __( 'Payment Pending: Your donation is currently processing.', 'give' );
			$notice_type    = 'warning';
			break;
		case 'refunded':
			$notice_message = __( 'Payment Refunded: Your donation has been refunded.', 'give' );
			$notice_type    = 'warning';
			break;
		case 'preapproval':
			$notice_message = __( 'Payment Preapproved: Thank you for your donation.', 'give' );
			$notice_type    = 'warning';
			break;
		case 'failed':
			$notice_message = __( 'Payment Failed: Please contact the site owner for assistance.', 'give' );
			$notice_type    = 'error';
			break;
		case 'cancelled':
			$notice_message = __( 'Payment Cancelled: Your donation has been cancelled.', 'give' );
			$notice_type    = 'error';
			break;
		case 'abandoned':
			$notice_message = __( 'Payment Abandoned: This donation has not been completed.', 'give' );
			$notice_type    = 'error';
			break;
		case 'revoked':
			$notice_message = __( 'Payment Revoked: Please contact the site owner for assistance.', 'give' );
			$notice_type    = 'error';
			break;
	}

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
	 * @param int $donation_id Donation ID.
	 */
	echo apply_filters( 'give_receipt_status_notice', Give_Notices::print_frontend_notice( $notice_message, false, $notice_type ), $id, $status, $donation_id );

}// End if().

/**
 * Fires in the donation receipt shortcode, before the receipt main table.
 *
 * Allows you to add elements before the table.
 *
 * @since 1.0
 *
 * @param object $donation          Donation object.
 * @param array  $give_receipt_args Receipt_argument.
 */
do_action( 'give_payment_receipt_before_table', $donation, $give_receipt_args );
?>

<table id="give_donation_receipt" class="give-table">
	<thead>
	<?php
	/**
	 * Fires in the donation receipt shortcode, before the receipt first header item.
	 *
	 * Allows you to add new <th> elements before the receipt first header item.
	 *
	 * @since 1.7
	 *
	 * @param object $donation          Donation object.
	 * @param array  $give_receipt_args Receipt_argument.
	 */
	do_action( 'give_payment_receipt_header_before', $donation, $give_receipt_args );
	?>
	<tr>
		<th scope="colgroup" colspan="2">
			<span class="give-receipt-thead-text"><?php esc_html_e( 'Donation Receipt', 'give' ); ?></span>
		</th>
	</tr>
	<?php
	/**
	 * Fires in the donation receipt shortcode, after the receipt last header item.
	 *
	 * Allows you to add new <th> elements after the receipt last header item.
	 *
	 * @since 1.7
	 *
	 * @param object $donation          Donation object.
	 * @param array  $give_receipt_args Receipt_argument.
	 */
	do_action( 'give_payment_receipt_header_after', $donation, $give_receipt_args );
	?>
	</thead>

	<tbody>
	<?php
	/**
	 * Fires in the donation receipt shortcode, before the receipt first item.
	 *
	 * Allows you to add new <td> elements before the receipt first item.
	 *
	 * @since 1.7
	 *
	 * @param object $donation          Donation object.
	 * @param array  $give_receipt_args Receipt_argument.
	 */
	do_action( 'give_payment_receipt_before', $donation, $give_receipt_args );
	?>

	<?php foreach ( $give_receipt_args['donation_receipt'] as $receipt_item ) { ?>
		<?php if ( filter_var( $receipt_item['display'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td scope="row"><strong><?php echo $receipt_item['name']; ?></strong></td>
				<td><?php echo $receipt_item['value']; ?></td>
			</tr>
		<?php endif; ?>
	<?php } ?>

	<?php
	/**
	 * Fires in the donation receipt shortcode, after the receipt last item.
	 *
	 * Allows you to add new <td> elements after the receipt last item.
	 *
	 * @since 1.7
	 *
	 * @param object $donation          Donation object.
	 * @param array  $give_receipt_args Receipt_argument.
	 */
	do_action( 'give_payment_receipt_after', $donation, $give_receipt_args );
	?>
	</tbody>
</table>

<?php
/**
 * Fires in the donation receipt shortcode, after the receipt main table.
 *
 * Allows you to add elements after the table.
 *
 * @since 1.7
 *
 * @param object $donation          Donation object.
 * @param array  $give_receipt_args Receipt_argument.
 */
do_action( 'give_payment_receipt_after_table', $donation, $give_receipt_args );
?>
