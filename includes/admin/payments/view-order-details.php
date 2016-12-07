<?php
/**
 * View Donation Details
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
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
	wp_die( esc_html__( 'Donation ID not supplied. Please try again.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 400 ) );
}

// Setup the variables
$payment_id = absint( $_GET['id'] );
$payment    = new Give_Payment( $payment_id );

// Sanity check... fail if donation ID is invalid
$payment_exists = $payment->ID;
if ( empty( $payment_exists ) ) {
	wp_die( esc_html__( 'The specified ID does not belong to a donation. Please try again.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 400 ) );
}

$number         = $payment->number;
$payment_meta   = $payment->get_meta();
$transaction_id = esc_attr( $payment->transaction_id );
$user_id        = $payment->user_id;
$customer_id    = $payment->customer_id;
$payment_date   = strtotime( $payment->date );
$user_info      = give_get_payment_meta_user_info( $payment_id );
$address        = $payment->address;
$gateway        = $payment->gateway;
$currency_code  = $payment->currency;
$gateway        = $payment->gateway;
$currency_code  = $payment->currency;
$payment_mode   = $payment->mode;
?>
<div class="wrap give-wrap">

	<h1 id="transaction-details-heading"><?php
		printf(
			/* translators: %s: donation number */
			esc_html__( 'Donation %s', 'give' ),
			$number
		);
		if ( $payment_mode == 'test' ) {
			echo '<span id="test-payment-label" class="give-item-label give-item-label-orange" data-tooltip="' . esc_attr__( 'This donation was made in test mode.', 'give' ) . '" data-tooltip-my-position="center left" data-tooltip-target-position="center right">' . esc_html__( 'Test Donation', 'give' ) . '</span>';
		}
		?></h1>

	<?php
	/**
	 * Fires in order details page, before the order form.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id Payment id.
	 */
	do_action( 'give_view_order_details_before', $payment_id );
	?>
	<form id="give-edit-order-form" method="post">
		<?php
		/**
		 * Fires in order details page, in the form before the order details.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_order_details_form_top', $payment_id );
		?>
		<div id="poststuff">
			<div id="give-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">

							<?php
							/**
							 * Fires in order details page, before the sidebar.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_order_details_sidebar_before', $payment_id );
							?>

							<div id="give-order-update" class="postbox give-order-data">

								<h3 class="hndle"><?php esc_html_e( 'Update Donation', 'give' ); ?></h3>

								<div class="inside">
									<div class="give-admin-box">

										<?php
										/**
										 * Fires in order details page, before the sidebar update-payment metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'give_view_order_details_totals_before', $payment_id );
										?>

										<div class="give-admin-box-inside">
											<p>
												<label for="give-payment-status" class="strong"><?php esc_html_e( 'Status:', 'give' ); ?></label>&nbsp;
												<select id="give-payment-status" name="give-payment-status" class="medium-text">
													<?php foreach ( give_get_payment_statuses() as $key => $status ) : ?>
														<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $payment->status, $key, true ); ?>><?php echo esc_html( $status ); ?></option>
													<?php endforeach; ?>
												</select>
												<span class="give-donation-status status-<?php echo sanitize_title( $payment->status ); ?>"><span class="give-donation-status-icon"></span></span>
											</p>
										</div>

										<div class="give-admin-box-inside">
											<p>
												<label for="give-payment-date" class="strong"><?php esc_html_e( 'Date:', 'give' ); ?></label>&nbsp;
												<input type="text" id="give-payment-date" name="give-payment-date" value="<?php echo esc_attr( date( 'm/d/Y', $payment_date ) ); ?>" class="medium-text give_datepicker"/>
											</p>
										</div>

										<div class="give-admin-box-inside">
											<p>
												<label for="give-payment-time-hour" class="strong"><?php esc_html_e( 'Time:', 'give' ); ?></label>&nbsp;
												<input type="number" step="1" max="24" id="give-payment-time-hour" name="give-payment-time-hour" value="<?php echo esc_attr( date_i18n( 'H', $payment_date ) ); ?>" class="small-text give-payment-time-hour"/>&nbsp;:&nbsp;
												<input type="number" step="1" max="59" id="give-payment-time-min" name="give-payment-time-min" value="<?php echo esc_attr( date( 'i', $payment_date ) ); ?>" class="small-text give-payment-time-min"/>
											</p>
										</div>

										<?php
										/**
										 * Fires in order details page, in the sidebar update-payment metabox.
										 *
										 * Allows you to add new inner items.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'give_view_order_details_update_inner', $payment_id );

										// @TODO: Fees
										$fees = give_get_payment_fees( $payment_id );
										if ( ! empty( $fees ) ) : ?>
											<div class="give-order-fees give-admin-box-inside">
												<p class="strong"><?php esc_html_e( 'Fees:', 'give' ); ?></p>
												<ul class="give-payment-fees">
													<?php foreach ( $fees as $fee ) : ?>
														<li>
															<span class="fee-label"><?php echo $fee['label']; ?>:</span>
															<span class="fee-amount" data-fee="<?php echo esc_attr( $fee['amount'] ); ?>"><?php echo give_currency_filter( $fee['amount'], $currency_code ); ?></span>
														</li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>

										<div class="give-order-payment give-admin-box-inside">
											<p>
												<label for="give-payment-total" class="strong"><?php esc_html_e( 'Total Donation:', 'give' ); ?></label>&nbsp;
												<?php echo give_currency_symbol( $payment->currency ); ?>
												&nbsp;<input id="give-payment-total" name="give-payment-total" type="text" class="small-text give-price-field" value="<?php echo esc_attr( give_format_decimal( give_get_payment_amount( $payment_id ) ) ); ?>"/>
											</p>
										</div>

										<?php
										/**
										 * Fires in order details page, after the sidebar update-donation metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'give_view_order_details_totals_after', $payment_id );
										?>

									</div>
									<!-- /.give-admin-box -->

								</div>
								<!-- /.inside -->

								<div class="give-order-update-box give-admin-box">
									<?php
									/**
									 * Fires in order details page, before the sidebar update-peyment metabox actions buttons.
									 *
									 * @since 1.0
									 *
									 * @param int $payment_id Payment id.
									 */
									do_action( 'give_view_order_details_update_before', $payment_id );
									?>

									<div id="major-publishing-actions">
										<div id="publishing-action">
											<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Donation', 'give' ); ?>"/>
											<?php if ( give_is_payment_complete( $payment_id ) ) : ?>
												<a href="<?php echo esc_url( add_query_arg( array(
													'give-action' => 'email_links',
													'purchase_id' => $payment_id,
												) ) ); ?>" id="give-resend-receipt" class="button-secondary right"><?php esc_html_e( 'Resend Receipt', 'give' ); ?></a>
											<?php endif; ?>
										</div>
										<div class="clear"></div>
									</div>

									<?php
									/**
									 * Fires in order details page, after the sidebar update-peyment metabox actions buttons.
									 *
									 * @since 1.0
									 *
									 * @param int $payment_id Payment id.
									 */
									do_action( 'give_view_order_details_update_after', $payment_id );
									?>

								</div>
								<!-- /.give-order-update-box -->

							</div>
							<!-- /#give-order-data -->

							<div id="give-order-details" class="postbox give-order-data">

								<h3 class="hndle"><?php esc_html_e( 'Donation Meta', 'give' ); ?></h3>

								<div class="inside">
									<div class="give-admin-box">

										<?php
										/**
										 * Fires in order details page, before the donation-meta metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'give_view_order_details_payment_meta_before', $payment_id );

										$gateway = give_get_payment_gateway( $payment_id );
										if ( $gateway ) : ?>
											<div class="give-order-gateway give-admin-box-inside">
												<p>
													<strong><?php esc_html_e( 'Gateway:', 'give' ); ?></strong>&nbsp;
													<?php echo give_get_gateway_admin_label( $gateway ); ?>
												</p>
											</div>
										<?php endif; ?>

										<div class="give-order-payment-key give-admin-box-inside">
											<p>
												<strong><?php esc_html_e( 'Key:', 'give' ); ?></strong>&nbsp;
												<?php echo give_get_payment_key( $payment_id ); ?>
											</p>
										</div>

										<div class="give-order-ip give-admin-box-inside">
											<p>
												<strong><?php esc_html_e( 'IP:', 'give' ); ?></strong>&nbsp;
												<?php echo esc_html( give_get_payment_user_ip( $payment_id ) ); ?>
											</p>
										</div>

										<?php if ( $transaction_id ) : ?>
											<div class="give-order-tx-id give-admin-box-inside">
												<p>
													<strong><?php esc_html_e( 'Donation ID:', 'give' ); ?></strong>&nbsp;
													<?php echo apply_filters( "give_payment_details_transaction_id-{$gateway}", $transaction_id, $payment_id ); ?>
												</p>
											</div>
										<?php endif; ?>

										<div class="give-admin-box-inside">
											<p><?php $purchase_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&user=' . urlencode( esc_attr( give_get_payment_user_email( $payment_id ) ) ) ); ?>
												<a href="<?php echo $purchase_url; ?>"><?php esc_html_e( 'View all donations for this donor &raquo;', 'give' ); ?></a>
											</p>
										</div>

										<?php
										/**
										 * Fires in order details page, after the donation-meta metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'give_view_order_details_payment_meta_after', $payment_id );
										?>

									</div>
									<!-- /.column-container -->

								</div>
								<!-- /.inside -->

							</div>
							<!-- /#give-order-data -->

							<?php
							/**
							 * Fires in order details page, after the sidebar.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_order_details_sidebar_after', $payment_id );
							?>

						</div>
						<!-- /#side-sortables -->
					</div>
					<!-- /#postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">

						<div id="normal-sortables" class="meta-box-sortables ui-sortable">

							<?php
							/**
							 * Fires in order details page, before the main area.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_order_details_main_before', $payment_id );
							?>

							<?php $column_count = 'columns-3'; ?>
							<div id="give-donation-overview" class="postbox <?php echo $column_count; ?>">
								<h3 class="hndle"><?php esc_html_e( 'Donation Information', 'give' ); ?></h3>

								<div class="inside">

									<div class="column-container">
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Donation Form ID:', 'give' ); ?></strong><br>
												<?php
												if ( $payment_meta['form_id'] ) :
													printf(
														'<a href="%1$s" target="_blank">#%2$s</a>',
														admin_url( 'post.php?action=edit&post=' . $payment_meta['form_id'] ),
														$payment_meta['form_id']
													);
												endif;
												?>
											</p>
											<p>
												<strong><?php esc_html_e( 'Donation Form Title:', 'give' ); ?></strong><br>
												<?php give_get_form_dropdown( array(
													'id'       => $payment_meta['form_id'],
													'selected' => $payment_meta['form_id'],
													'chosen'   => true,
												), true ); ?>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Donation Date:', 'give' ); ?></strong><br>
												<?php echo date_i18n( give_date_format(), $payment_date ); ?>
											</p>
											<p>
												<strong><?php esc_html_e( 'Donation Level:', 'give' ); ?></strong><br>
												<span class="give-donation-level">
													<?php
													$var_prices = give_has_variable_prices( $payment_meta['form_id'] );
													if ( empty( $var_prices ) ) {
														esc_html_e( 'n/a', 'give' );
													} else {
														$prices_atts = '';
														if( $variable_prices = give_get_variable_prices( $payment_meta['form_id'] ) ) {
															foreach ( $variable_prices as $variable_price ) {
																$prices_atts[$variable_price['_give_id']['level_id']] = give_format_amount( $variable_price['_give_amount'] );
															}
														}
														// Variable price dropdown options.
														$variable_price_dropdown_option = array(
															'id'               => $payment_meta['form_id'],
															'name'             => 'give-variable-price',
															'chosen'           => true,
															'show_option_all'  => '',
															'show_option_none' => '',
															'select_atts'      => 'data-prices=' . esc_attr( json_encode( $prices_atts ) ),
															'selected'         => $payment_meta['price_id'],
														);
														// Render variable prices select tag html.
														give_get_form_variable_price_dropdown( $variable_price_dropdown_option, true );
													}
													?>
												</span>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Total Donation:', 'give' ); ?></strong><br>
												<?php echo esc_html( give_currency_filter( give_format_amount( give_get_payment_amount( $payment_id ) ) ) ); ?>
											</p>
											<p>
												<?php
												/**
												 * Fires in order details page, in the donation-information metabox, before the head elements.
												 *
												 * Allows you to add new TH elements at the beginning.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'give_donation_details_thead_before', $payment_id );


												/**
												 * Fires in order details page, in the donation-information metabox, after the head elements.
												 *
												 * Allows you to add new TH elements at the end.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'give_donation_details_thead_after', $payment_id );

												/**
												 * Fires in order details page, in the donation-information metabox, before the body elements.
												 *
												 * Allows you to add new TD elements at the beginning.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'give_donation_details_tbody_before', $payment_id );

												/**
												 * Fires in order details page, in the donation-information metabox, after the body elements.
												 *
												 * Allows you to add new TD elements at the end.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'give_donation_details_tbody_after', $payment_id );
												?>
											</p>
										</div>
									</div>

								</div>
								<!-- /.inside -->

							</div>
							<!-- /#give-donation-overview -->

							<?php
							/**
							 * Fires in order details page, after the files metabox.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_order_details_files_after', $payment_id );
							?>

							<div id="give-donor-details" class="postbox">
								<h3 class="hndle"><?php esc_html_e( 'Donor Details', 'give' ); ?></h3>

								<div class="inside">

									<?php $customer = new Give_Customer( $customer_id ); ?>

									<div class="column-container customer-info">
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Donor ID:', 'give' ); ?></strong><br>
												<?php
												if ( ! empty( $customer->id ) ) {
													printf(
														'<a href="%1$s" target="_blank">#%2$s</a>',
														admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $customer->id ),
														$customer->id
													);
												}
												?>
											</p>
											<p>
												<strong><?php esc_html_e( 'Donor Since:', 'give' ); ?></strong><br>
												<?php echo date_i18n( give_date_format(), strtotime( $customer->date_created ) ) ?>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Donor Name:', 'give' ); ?></strong><br>
												<?php echo $customer->name; ?>
											</p>
											<p>
												<strong><?php esc_html_e( 'Donor Email:', 'give' ); ?></strong><br>
												<?php echo $customer->email; ?>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Change Donor:', 'give' ); ?></strong><br>
												<?php
												echo Give()->html->donor_dropdown( array(
													'selected' => $customer->id,
													'name'     => 'customer-id',
												) );
												?>
											</p>
											<p>
												<a href="#new" class="give-payment-new-customer"><?php esc_html_e( 'Create New Donor', 'give' ); ?></a>
											</p>
										</div>
									</div>

									<div class="column-container new-customer" style="display: none">
										<div class="column">
											<p>
												<label for="give-new-customer-name"><?php esc_html_e( 'New Donor Name:', 'give' ); ?></label>
												<input id="give-new-customer-name" type="text" name="give-new-customer-name" value="" class="medium-text"/>
											</p>
										</div>
										<div class="column">
											<p>
												<label for="give-new-customer-email"><?php esc_html_e( 'New Donor Email:', 'give' ); ?></label>
												<input id="give-new-customer-email" type="email" name="give-new-customer-email" value="" class="medium-text"/>
											</p>
										</div>
										<div class="column">
											<p>
												<input type="hidden" name="give-current-customer" value="<?php echo $customer->id; ?>"/>
												<input type="hidden" id="give-new-customer" name="give-new-customer" value="0"/>
												<a href="#cancel" class="give-payment-new-customer-cancel give-delete"><?php esc_html_e( 'Cancel', 'give' ); ?></a>
												<br>
												<em><?php esc_html_e( 'Click "Save Donation" to create new donor.', 'give' ); ?></em>
											</p>
										</div>
									</div>

									<?php
									/**
									 * Fires in order details page, in the donor-details metabox.
									 *
									 * The hook is left here for backwards compatibility.
									 *
									 * @since 1.7
									 *
									 * @param array $payment_meta Payment meta.
									 * @param array $user_info    User information.
									 */
									do_action( 'give_payment_personal_details_list', $payment_meta, $user_info );

									/**
									 * Fires in order details page, in the donor-details metabox.
									 *
									 * @since 1.7
									 *
									 * @param int $payment_id Payment id.
									 */
									do_action( 'give_payment_view_details', $payment_id );
									?>

								</div>
								<!-- /.inside -->
							</div>
							<!-- /#give-donor-details -->

							<?php
							/**
							 * Fires in order details page, before the billing metabox.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_order_details_billing_before', $payment_id );
							?>

							<div id="give-billing-details" class="postbox">
								<h3 class="hndle"><?php esc_html_e( 'Billing Address', 'give' ); ?></h3>

								<div class="inside">

									<div id="give-order-address">

										<div class="order-data-address">
											<div class="data column-container">
												<div class="column">
													<div class="give-wrap-address-line1">
														<label for="give-payment-address-line1" class="order-data-address"><?php esc_html_e( 'Address 1:', 'give' ); ?></label>
														<input id="give-payment-address-line1" type="text" name="give-payment-address[0][line1]" value="<?php echo esc_attr( $address['line1'] ); ?>" class="medium-text"/>
													</div>
													<div class="give-wrap-address-line2">
														<label for="give-payment-address-line2" class="order-data-address-line"><?php esc_html_e( 'Address 2:', 'give' ); ?></label>
														<input id="give-payment-address-line2" type="text" name="give-payment-address[0][line2]" value="<?php echo esc_attr( $address['line2'] ); ?>" class="medium-text"/>
													</div>
												</div>
												<div class="column">
													<div class="give-wrap-address-city">
														<label for="give-payment-address-city" class="order-data-address-line"><?php esc_html_e( 'City:', 'give' ); ?></label>
														<input id="give-payment-address-city" type="text" name="give-payment-address[0][city]" value="<?php echo esc_attr( $address['city'] ); ?>" class="medium-text"/>
													</div>
													<div class="give-wrap-address-zip">
														<label for="give-payment-address-zip" class="order-data-address-line"><?php esc_html_e( 'Zip / Postal Code:', 'give' ); ?></label>
														<input id="give-payment-address-zip" type="text" name="give-payment-address[0][zip]" value="<?php echo esc_attr( $address['zip'] ); ?>" class="medium-text"/>

													</div>
												</div>
												<div class="column">
													<div id="give-order-address-country-wrap">
														<label class="order-data-address-line"><?php esc_html_e( 'Country:', 'give' ); ?></label>
														<?php
														echo Give()->html->select( array(
															'options'          => give_get_country_list(),
															'name'             => 'give-payment-address[0][country]',
															'selected'         => $address['country'],
															'show_option_all'  => false,
															'show_option_none' => false,
															'chosen'           => true,
															'placeholder'      => esc_attr__( 'Select a country', 'give' ),
														) );
														?>
													</div>
													<div id="give-order-address-state-wrap">
														<label for="give-payment-address-state" class="order-data-address-line"><?php esc_html_e( 'State / Province:', 'give' ); ?></label>
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
																'placeholder'      => esc_attr__( 'Select a state', 'give' ),
															) );
														} else {
															?>
															<input id="give-payment-address-state" type="text" name="give-payment-address[0][state]" value="<?php echo esc_attr( $address['state'] ); ?>" class="medium-text"/>
															<?php
														} ?>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- /#give-order-address -->

									<?php
									/**
									 * Fires in order details page, in the billing metabox, after all the fields.
									 *
									 * Allows you to insert new billing address fields.
									 *
									 * @since 1.7
									 *
									 * @param int $payment_id Payment id.
									 */
									do_action( 'give_payment_billing_details', $payment_id );
									?>

								</div>
								<!-- /.inside -->
							</div>
							<!-- /#give-billing-details -->

							<?php
							/**
							 * Fires in order details page, after the billing metabox.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_order_details_billing_after', $payment_id );
							?>

							<div id="give-payment-notes" class="postbox">
								<h3 class="hndle"><?php esc_html_e( 'Donation Notes', 'give' ); ?></h3>

								<div class="inside">
									<div id="give-payment-notes-inner">
										<?php
										$notes = give_get_payment_notes( $payment_id );
										if ( ! empty( $notes ) ) {
											$no_notes_display = ' style="display:none;"';
											foreach ( $notes as $note ) :

												echo give_get_payment_note_html( $note, $payment_id );

											endforeach;
										} else {
											$no_notes_display = '';
										}
										echo '<p class="give-no-payment-notes"' . $no_notes_display . '>' . esc_html__( 'No donation notes.', 'give' ) . '</p>'; ?>
									</div>
									<textarea name="give-payment-note" id="give-payment-note" class="large-text"></textarea>

									<div class="give-clearfix">
										<button id="give-add-payment-note" class="button button-secondary button-small" data-payment-id="<?php echo absint( $payment_id ); ?>"><?php esc_html_e( 'Add Note', 'give' ); ?></button>
									</div>

								</div>
								<!-- /.inside -->
							</div>
							<!-- /#give-payment-notes -->

							<?php
							/**
							 * Fires in order details page, after the main area.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_order_details_main_after', $payment_id );
							?>

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

		<?php
		/**
		 * Fires in order details page, in the form after the order details.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_order_details_form_bottom', $payment_id );

		wp_nonce_field( 'give_update_payment_details_nonce' );
		?>
		<input type="hidden" name="give_payment_id" value="<?php echo esc_attr( $payment_id ); ?>"/>
		<input type="hidden" name="give_action" value="update_payment_details"/>
	</form>
	<?php
	/**
	 * Fires in order details page, after the order form.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id Payment id.
	 */
	do_action( 'give_view_order_details_after', $payment_id );
	?>
</div><!-- /.wrap -->
