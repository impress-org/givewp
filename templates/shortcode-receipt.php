<?php
/**
 * This template is used to display the purchase summary with [give_receipt]
 */
global $give_receipt_args, $payment;

//Validation: Ensure $payment var is set
if ( empty( $payment ) && isset( $give_receipt_args['id'] ) ) {
	$payment = get_post( $give_receipt_args['id'] );
}

//Double-Validation: Check for $payment global
if ( empty( $payment ) ) {
	give_output_error( esc_html__( 'The specified receipt ID appears to be invalid', 'give' ) );

	return;
}

$meta     = give_get_payment_meta( $payment->ID );
$donation = give_get_payment_form_title( $meta );
$user     = give_get_payment_meta_user_info( $payment->ID );
$email    = give_get_payment_user_email( $payment->ID );
$status   = give_get_payment_status( $payment, true );
?>

<?php do_action( 'give_payment_receipt_before_table', $payment, $give_receipt_args ); ?>

	<table id="give_donation_receipt" class="give-table">
		<thead>
		<?php do_action( 'give_payment_receipt_before', $payment, $give_receipt_args ); ?>
		<tr>
			<th colspan="2">
				<span class="give-receipt-thead-text"><?php esc_html_e('Donation Receipt', 'give') ?></span>
			</th>
		</tr>
		</thead>

		<tbody>

		<?php if ( filter_var( $give_receipt_args['donor'], FILTER_VALIDATE_BOOLEAN ) ) : ?>

			<tr>
				<td><strong><?php esc_html_e( 'Donor', 'give' ); ?>:</strong></td>
				<td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
			</tr>

		<?php endif; ?>

		<?php if ( filter_var( $give_receipt_args['date'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Date', 'give' ); ?>:</strong></td>
				<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $meta['date'] ) ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $give_receipt_args['price'], FILTER_VALIDATE_BOOLEAN ) ) : ?>

			<tr>
				<td><strong><?php esc_html_e( 'Total Donation', 'give' ); ?>:</strong></td>
				<td><?php echo give_payment_amount( $payment->ID ); ?></td>
			</tr>

		<?php endif; ?>
		
		<tr>
			<td class="give_receipt_payment_status"><strong><?php esc_html_e( 'Donation', 'give' ); ?>:</strong></td>
			<td class="give_receipt_payment_status"><?php echo $donation; ?></td>
		</tr>

		<tr>
			<td class="give_receipt_payment_status"><strong><?php esc_html_e( 'Donation Status', 'give' ); ?>:</strong></td>
			<td class="give_receipt_payment_status <?php echo strtolower( $status ); ?>"><?php echo $status; ?></td>
		</tr>

		<?php if ( filter_var( $give_receipt_args['payment_id'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Transaction ID', 'give' ); ?>:</strong></td>
				<td><?php echo give_get_payment_number( $payment->ID ); ?></td>
			</tr>
		<?php else : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Payment', 'give' ); ?>:</strong></td>
				<td><?php esc_html_e( 'Details', 'give' ); ?>:</td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $give_receipt_args['payment_key'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Payment Key', 'give' ); ?>:</strong></td>
				<td><?php echo get_post_meta( $payment->ID, '_give_payment_purchase_key', true ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $give_receipt_args['payment_method'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Payment Method', 'give' ); ?>:</strong></td>
				<td><?php echo give_get_gateway_checkout_label( give_get_payment_gateway( $payment->ID ) ); ?></td>
			</tr>
		<?php endif; ?>

		<?php
		//No fees built in just yet...
		//@TODO: Fees
		if ( ( $fees = give_get_payment_fees( $payment->ID, 'fee' ) ) ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Fees', 'give' ); ?>:</strong></td>
				<td>
					<ul class="give_receipt_fees">
						<?php foreach ( $fees as $fee ) : ?>
							<li>
								<span class="give_fee_label"><?php echo esc_html( $fee['label'] ); ?></span>
								<span class="give_fee_sep">&nbsp;&ndash;&nbsp;</span>
								<span class="give_fee_amount"><?php echo give_currency_filter( give_format_amount( $fee['amount'] ) ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</td>
			</tr>
		<?php endif; ?>

		<?php do_action( 'give_payment_receipt_after', $payment, $give_receipt_args ); ?>
		</tbody>
	</table>

<?php do_action( 'give_payment_receipt_after_table', $payment, $give_receipt_args ); ?>