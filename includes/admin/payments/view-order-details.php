<?php
/**
 * View Donation Details
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * View Order Details Page
 *
 * @since 1.0
 * @return void
 */
if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
	wp_die( __( 'Donation ID not supplied. Please try again', 'give' ), __( 'Error', 'give' ) );
}

// Setup the variables
$payment_id = absint( $_GET['id'] );
$number     = give_get_payment_number( $payment_id );
$item       = get_post( $payment_id );

// Sanity check... fail if purchase ID is invalid
if ( ! is_object( $item ) || $item->post_type != 'give_payment' ) {
	wp_die( __( 'The specified ID does not belong to a payment. Please try again', 'give' ), __( 'Error', 'give' ) );
}

$payment_meta   = give_get_payment_meta( $payment_id );
$transaction_id = esc_attr( give_get_payment_transaction_id( $payment_id ) );
$user_id        = give_get_payment_user_id( $payment_id );
$donor_id       = give_get_payment_customer_id( $payment_id );
$payment_date   = strtotime( $item->post_date );
$user_info      = give_get_payment_meta_user_info( $payment_id );
$address        = ! empty( $user_info['address'] ) ? $user_info['address'] : array(
	'line1'   => '',
	'line2'   => '',
	'city'    => '',
	'country' => '',
	'state'   => '',
	'zip'     => ''
);
$gateway        = give_get_payment_gateway( $payment_id );
$currency_code  = give_get_payment_currency_code( $payment_id );
?>
<div class="wrap give-wrap">
	<h2><?php printf( __( 'Payment %s', 'give' ), $number ); ?></h2>
	<?php do_action( 'give_view_order_details_before', $payment_id ); ?>
	<form id="give-edit-order-form" method="post">
		<?php do_action( 'give_view_order_details_form_top', $payment_id ); ?>
		<div id="poststuff">
			<div id="give-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">

							<?php do_action( 'give_view_order_details_sidebar_before', $payment_id ); ?>


							<div id="give-order-update" class="postbox give-order-data">

								<h3 class="hndle">
									<span><?php _e( 'Update Payment', 'give' ); ?></span>
								</h3>

								<div class="inside">
									<div class="give-admin-box">

										<?php do_action( 'give_view_order_details_totals_before', $payment_id ); ?>

										<div class="give-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Status:', 'give' ); ?></span>&nbsp;
												<select name="give-payment-status" class="medium-text">
													<?php foreach ( give_get_payment_statuses() as $key => $status ) : ?>
														<option value="<?php echo esc_attr( $key ); ?>"<?php selected( give_get_payment_status( $item, true ), $status ); ?>><?php echo esc_html( $status ); ?></option>
													<?php endforeach; ?>
												</select>
												<span class="give-donation-status status-<?php echo sanitize_title( give_get_payment_status( $item, true ) ); ?>"><span class="give-donation-status-icon"></span></span>
											</p>
										</div>

										<div class="give-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Date:', 'give' ); ?></span>&nbsp;
												<input type="text" name="give-payment-date" value="<?php echo esc_attr( date( 'm/d/Y', $payment_date ) ); ?>" class="medium-text give_datepicker" />
											</p>
										</div>

										<div class="give-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Time:', 'give' ); ?></span>&nbsp;
												<input type="number" step="1" max="24" name="give-payment-time-hour" value="<?php echo esc_attr( date_i18n( 'H', $payment_date ) ); ?>" class="small-text give-payment-time-hour" />&nbsp;:&nbsp;
												<input type="number" step="1" max="59" name="give-payment-time-min" value="<?php echo esc_attr( date( 'i', $payment_date ) ); ?>" class="small-text give-payment-time-min" />
											</p>
										</div>

										<?php do_action( 'give_view_order_details_update_inner', $payment_id ); ?>

										<?php
										$fees = give_get_payment_fees( $payment_id );
										if ( ! empty( $fees ) ) : ?>
											<div class="give-order-fees give-admin-box-inside">
												<p class="strong"><?php _e( 'Fees', 'give' ); ?>:</p>
												<ul class="give-payment-fees">
													<?php foreach ( $fees as $fee ) : ?>
														<li>
															<span class="fee-label"><?php echo $fee['label'] . ':</span> ' . '<span class="fee-amount" data-fee="' . esc_attr( $fee['amount'] ) . '">' . give_currency_filter( $fee['amount'], $currency_code ); ?></span>
														</li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>


										<div class="give-order-payment give-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Total Donation', 'give' ); ?>:</span>&nbsp;
												<?php echo give_currency_symbol( $payment_meta['currency'] ); ?>&nbsp;<input name="give-payment-total" type="text" class="small-text" value="<?php echo esc_attr( give_format_amount( give_get_payment_amount( $payment_id ) ) ); ?>" />
											</p>
										</div>

										<?php do_action( 'give_view_order_details_totals_after', $payment_id ); ?>

									</div>
									<!-- /.give-admin-box -->

								</div>
								<!-- /.inside -->

								<div class="give-order-update-box give-admin-box">
									<?php do_action( 'give_view_order_details_update_before', $payment_id ); ?>
									<div id="major-publishing-actions">
										<div id="publishing-action">
											<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Payment', 'give' ); ?>" />
											<?php if ( give_is_payment_complete( $payment_id ) ) : ?>
												<a href="<?php echo esc_url( add_query_arg( array(
													'give-action' => 'email_links',
													'purchase_id' => $payment_id
												) ) ); ?>" id="give-resend-receipt" class="button-secondary right"><?php _e( 'Resend Receipt', 'give' ); ?></a>
											<?php endif; ?>
										</div>
										<div class="clear"></div>
									</div>
									<?php do_action( 'give_view_order_details_update_after', $payment_id ); ?>
								</div>
								<!-- /.give-order-update-box -->

							</div>
							<!-- /#give-order-data -->

							<div id="give-order-details" class="postbox give-order-data">

								<h3 class="hndle">
									<span><?php _e( 'Payment Meta', 'give' ); ?></span>
								</h3>

								<div class="inside">
									<div class="give-admin-box">

										<?php do_action( 'give_view_order_details_payment_meta_before', $payment_id ); ?>

										<?php
										$gateway = give_get_payment_gateway( $payment_id );
										if ( $gateway ) : ?>
											<div class="give-order-gateway give-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Gateway:', 'give' ); ?></span>&nbsp;
													<?php echo give_get_gateway_admin_label( $gateway ); ?>
												</p>
											</div>
										<?php endif; ?>

										<div class="give-order-payment-key give-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Key:', 'give' ); ?></span>&nbsp;
												<span><?php echo give_get_payment_key( $payment_id ); ?></span>
											</p>
										</div>

										<div class="give-order-ip give-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'IP:', 'give' ); ?></span>&nbsp;
												<span><?php echo esc_html( give_get_payment_user_ip( $payment_id ) ); ?></span>
											</p>
										</div>

										<?php if ( $transaction_id ) : ?>
											<div class="give-order-tx-id give-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Transaction ID:', 'give' ); ?></span>&nbsp;
													<span><?php echo apply_filters( 'give_payment_details_transaction_id-' . $gateway, $transaction_id, $payment_id ); ?></span>
												</p>
											</div>
										<?php endif; ?>

										<div class="give-admin-box-inside">
											<p><?php $purchase_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&user=' . esc_attr( give_get_payment_user_email( $payment_id ) ) ); ?>
												<a href="<?php echo $purchase_url; ?>"><?php _e( 'View all donations for this donor', 'give' ); ?> &raquo;</a>
											</p>
										</div>

										<?php do_action( 'give_view_order_details_payment_meta_after', $payment_id ); ?>

									</div>
									<!-- /.column-container -->

								</div>
								<!-- /.inside -->

							</div>
							<!-- /#give-order-data -->

							<?php do_action( 'give_view_order_details_sidebar_after', $payment_id ); ?>

						</div>
						<!-- /#side-sortables -->
					</div>
					<!-- /#postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">

						<div id="normal-sortables" class="meta-box-sortables ui-sortable">

							<?php do_action( 'give_view_order_details_main_before', $payment_id ); ?>

							<?php $column_count = 'columns-3'; ?>
							<div id="give-donation-overview" class="postbox <?php echo $column_count; ?>">
								<h3 class="hndle">
									<span><?php _e( 'Donation Information', 'give' ); ?></span>
								</h3>

								<div class="inside">

									<table style="width:100%;text-align:left;">
										<thead>
										<tr>
											<?php do_action( 'give_donation_details_thead_before', $payment_id ); ?>
											<th><?php _e( 'Form ID', 'give' ) ?></th>
											<th><?php _e( 'Form Title', 'give' ) ?></th>
											<th><?php _e( 'Date and Time', 'give' ) ?></th>
											<th><?php _e( 'Total Donation', 'give' ) ?></th>
											<?php do_action( 'give_donation_details_thead_after', $payment_id ); ?>
										</tr>
										</thead>
										<tr>
											<?php do_action( 'give_donation_details_tbody_before', $payment_id ); ?>
											<td>
												<?php echo $payment_meta['form_id']; ?>
											</td>
											<td>
												<a href="<?php echo get_permalink( $payment_meta['form_id'] ); ?>"><?php echo $payment_meta['form_title']; ?></a>
											</td>
											<td><?php echo date( 'm/d/Y', $payment_date ) . ' ' . date_i18n( 'H:i', $payment_date ); ?></td>
											<td><?php echo esc_html( give_currency_filter( give_format_amount( give_get_payment_amount( $payment_id ) ) ) ); ?></td>
											<?php do_action( 'give_donation_details_tbody_after', $payment_id ); ?>

										</tr>
									</table>

								</div>
								<!-- /.inside -->


							</div>
							<!-- /#give-donation-overview -->

							<?php do_action( 'give_view_order_details_files_after', $payment_id ); ?>

							<?php do_action( 'give_view_order_details_billing_before', $payment_id ); ?>


							<div id="give-customer-details" class="postbox">
								<h3 class="hndle">
									<span><?php _e( 'Donor Details', 'give' ); ?></span>
								</h3>

								<div class="inside give-clearfix">

									<?php $customer = new Give_Customer( give_get_payment_customer_id( $payment_id ) ); ?>

									<div class="column-container customer-info">
										<div class="column">
											<?php echo Give()->html->donor_dropdown( array(
												'selected' => $customer->id,
												'name'     => 'customer-id'
											) ); ?>
										</div>
										<div class="column">
											<input type="hidden" name="give-current-customer" value="<?php echo $customer->id; ?>" />
										</div>
										<div class="column">
											<?php if ( ! empty( $customer->id ) ) : ?>
												<?php $customer_url = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $customer->id ); ?>
												<a href="<?php echo $customer_url; ?>" title="<?php _e( 'View Donor Details', 'give' ); ?>"><?php _e( 'View Donor Details', 'give' ); ?></a>
												&nbsp;|&nbsp;
											<?php endif; ?>
											<a href="#new" class="give-payment-new-customer" title="<?php _e( 'New Donor', 'give' ); ?>"><?php _e( 'New Donor', 'give' ); ?></a>
										</div>
									</div>

									<div class="column-container new-customer" style="display: none">
										<div class="column">
											<strong><?php _e( 'Name:', 'give' ); ?></strong>&nbsp;
											<input type="text" name="give-new-customer-name" value="" class="medium-text" />
										</div>
										<div class="column">
											<strong><?php _e( 'Email:', 'give' ); ?></strong>&nbsp;
											<input type="email" name="give-new-customer-email" value="" class="medium-text" />
										</div>
										<div class="column">
											<input type="hidden" id="give-new-customer" name="give-new-customer" value="0" />
											<a href="#cancel" class="give-payment-new-customer-cancel give-delete"><?php _e( 'Cancel', 'give' ); ?></a>
										</div>
										<div class="column">
											<small>
												<em>*<?php _e( 'Click "Save Payment" to create new donor', 'give' ); ?></em>
											</small>
										</div>
									</div>

									<?php
									// The give_payment_personal_details_list hook is left here for backwards compatibility
									do_action( 'give_payment_personal_details_list', $payment_meta, $user_info );
									do_action( 'give_payment_view_details', $payment_id );
									?>

								</div>
								<!-- /.inside -->
							</div>
							<!-- /#give-customer-details -->


							<div id="give-billing-details" class="postbox">
								<h3 class="hndle">
									<span><?php _e( 'Billing Address', 'give' ); ?></span>
								</h3>

								<div class="inside give-clearfix">

									<div id="give-order-address">

										<div class="order-data-address">
											<div class="data column-container">
												<div class="column">
													<p>
														<strong class="order-data-address-line"><?php _e( 'Street Address Line 1:', 'give' ); ?></strong><br />
														<input type="text" name="give-payment-address[0][line1]" value="<?php echo esc_attr( $address['line1'] ); ?>" class="medium-text" />
													</p>

													<p>
														<strong class="order-data-address-line"><?php _e( 'Street Address Line 2:', 'give' ); ?></strong><br />
														<input type="text" name="give-payment-address[0][line2]" value="<?php echo esc_attr( $address['line2'] ); ?>" class="medium-text" />
													</p>

												</div>
												<div class="column">
													<p>
														<strong class="order-data-address-line"><?php echo _x( 'City:', 'Address City', 'give' ); ?></strong><br />
														<input type="text" name="give-payment-address[0][city]" value="<?php echo esc_attr( $address['city'] ); ?>" class="medium-text" />

													</p>

													<p>
														<strong class="order-data-address-line"><?php echo _x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'give' ); ?></strong><br />
														<input type="text" name="give-payment-address[0][zip]" value="<?php echo esc_attr( $address['zip'] ); ?>" class="medium-text" />

													</p>
												</div>
												<div class="column">
													<p id="give-order-address-country-wrap">
														<strong class="order-data-address-line"><?php echo _x( 'Country:', 'Address country', 'give' ); ?></strong><br />
														<?php
														echo Give()->html->select( array(
															'options'          => give_get_country_list(),
															'name'             => 'give-payment-address[0][country]',
															'selected'         => $address['country'],
															'show_option_all'  => false,
															'show_option_none' => false,
															'chosen'           => true,
															'placeholder'      => __( 'Select a country', 'give' )
														) );
														?>
													</p>

													<p id="give-order-address-state-wrap">
														<strong class="order-data-address-line"><?php echo _x( 'State / Province:', 'State / province of address', 'give' ); ?></strong><br />
														<?php
														$states = give_get_states( $address['country'] );
														if ( ! empty( $states ) ) {
															echo Give()->html->select( array(
																'options'          => $states,
																'name'             => 'give-payment-address[0][state]',
																'selected'         => $address['state'],
																'show_option_all'  => false,
																'show_option_none' => false,
																'chosen'           => true,
																'placeholder'      => __( 'Select a state', 'give' )
															) );
														} else {
															?>
															<input type="text" name="give-payment-address[0][state]" value="<?php echo esc_attr( $address['state'] ); ?>" class="medium-text" />
														<?php
														} ?>
													</p>
												</div>
											</div>
										</div>
									</div>
									<!-- /#give-order-address -->

									<?php do_action( 'give_payment_billing_details', $payment_id ); ?>

								</div>
								<!-- /.inside -->
							</div>
							<!-- /#give-billing-details -->

							<?php do_action( 'give_view_order_details_billing_after', $payment_id ); ?>

							<div id="give-payment-notes" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Payment Notes', 'give' ); ?></span></h3>

								<div class="inside">
									<div id="give-payment-notes-inner">
										<?php
										$notes = give_get_payment_notes( $payment_id );
										if ( ! empty( $notes ) ) :
											$no_notes_display = ' style="display:none;"';
											foreach ( $notes as $note ) :

												echo give_get_payment_note_html( $note, $payment_id );

											endforeach;
										else :
											$no_notes_display = '';
										endif;
										echo '<p class="give-no-payment-notes"' . $no_notes_display . '>' . __( 'No payment notes', 'give' ) . '</p>';
										?>
									</div>
									<textarea name="give-payment-note" id="give-payment-note" class="large-text"></textarea>

									<p class="give-clearfix">
										<button id="give-add-payment-note" class="button button-secondary button-small" data-payment-id="<?php echo absint( $payment_id ); ?>"><?php _e( 'Add Note', 'give' ); ?></button>
									</p>

									<div class="clear"></div>
								</div>
								<!-- /.inside -->
							</div>
							<!-- /#give-payment-notes -->

							<?php do_action( 'give_view_order_details_main_after', $payment_id ); ?>
						</div>
						<!-- /#normal-sortables -->
					</div>
					<!-- #postbox-container-2 -->
				</div>
				<!-- /#post-body -->
			</div>
			<!-- #give-dashboard-widgets-wrap -->
		</div>
		<!-- /#post-stuff -->
		<?php do_action( 'give_view_order_details_form_bottom', $payment_id ); ?>
		<?php wp_nonce_field( 'give_update_payment_details_nonce' ); ?>
		<input type="hidden" name="give_payment_id" value="<?php echo esc_attr( $payment_id ); ?>" />
		<input type="hidden" name="give_action" value="update_payment_details" />
	</form>
	<?php do_action( 'give_view_order_details_after', $payment_id ); ?>
</div><!-- /.wrap -->
