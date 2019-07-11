<?php
/**
 * View Donation Details
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'view_give_payments' ) ) {
	wp_die(
		__( 'Sorry, you are not allowed to access this page.', 'give' ), __( 'Error', 'give' ), array(
			'response' => 403,
		)
	);
}

/**
 * View donation details page
 *
 * @since 1.0
 * @return void
 */
if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
	wp_die( __( 'Donation ID not supplied. Please try again.', 'give' ), __( 'Error', 'give' ), array( 'response' => 400 ) );
}

// Setup the variables
$payment_id = absint( $_GET['id'] );
$payment    = new Give_Payment( $payment_id );

// Sanity check... fail if donation ID is invalid
$payment_exists = $payment->ID;
if ( empty( $payment_exists ) ) {
	wp_die( __( 'The specified ID does not belong to a donation. Please try again.', 'give' ), __( 'Error', 'give' ), array( 'response' => 400 ) );
}

$number       = $payment->number;
$payment_meta = $payment->get_meta();

$company_name   = ! empty( $payment_meta['_give_donation_company'] ) ? esc_attr( $payment_meta['_give_donation_company'] ) : '';
$transaction_id = esc_attr( $payment->transaction_id );
$user_id        = $payment->user_id;
$donor_id       = $payment->customer_id;
$payment_date   = strtotime( $payment->date );
$user_info      = give_get_payment_meta_user_info( $payment_id );
$address        = $payment->address;
$currency_code  = $payment->currency;
$gateway        = $payment->gateway;
$currency_code  = $payment->currency;
$payment_mode   = $payment->mode;
$base_url       = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' );

?>
<div class="wrap give-wrap">

	<h1 id="transaction-details-heading" class="wp-heading-inline">
		<?php
		printf(
		/* translators: %s: donation number */
			esc_html__( 'Donation %s', 'give' ),
			$number
		);
		if ( $payment_mode == 'test' ) {
			echo Give()->tooltips->render_span(array(
				'label' => __( 'This donation was made in test mode.', 'give' ),
				'tag_content' => __( 'Test Donation', 'give' ),
				'position'=> 'right',
				'attributes' => array(
					'id' => 'test-payment-label',
					'class' => 'give-item-label give-item-label-orange'
				)
			));
		}
		?>
	</h1>

	<?php
	/**
	 * Fires in donation details page, before the page content and after the H1 title output.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id Payment id.
	 */
	do_action( 'give_view_donation_details_before', $payment_id );
	?>

	<hr class="wp-header-end">

	<form id="give-edit-order-form" method="post">
		<?php
		/**
		 * Fires in donation details page, in the form before the order details.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_form_top', $payment_id );
		?>
		<div id="poststuff" class="give-clearfix">
			<div id="give-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">

							<?php
							/**
							 * Fires in donation details page, before the sidebar.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_donation_details_sidebar_before', $payment_id );
							?>

							<div id="give-order-update" class="postbox give-order-data">

								<div class="give-order-top">
									<h3 class="hndle"><?php _e( 'Update Donation', 'give' ); ?></h3>

									<?php
									if ( current_user_can( 'view_give_payments' ) ) {
										echo sprintf(
											'<span class="delete-donation" id="delete-donation-%d"><a class="delete-single-donation delete-donation-button dashicons dashicons-trash" href="%s" aria-label="%s"></a></span>',
											$payment_id,
											wp_nonce_url(
												add_query_arg(
													array(
														'give-action' => 'delete_payment',
														'purchase_id' => $payment_id,
													), $base_url
												), 'give_donation_nonce'
											),
											sprintf( __( 'Delete Donation %s', 'give' ), $payment_id )
										);
									}
									?>
								</div>

								<div class="inside">
									<div class="give-admin-box">

										<?php
										/**
										 * Fires in donation details page, before the sidebar update-payment metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'give_view_donation_details_totals_before', $payment_id );
										?>

										<div class="give-admin-box-inside">
											<p>
												<label for="give-payment-status" class="strong"><?php _e( 'Status:', 'give' ); ?></label>&nbsp;
												<select id="give-payment-status" name="give-payment-status" class="medium-text">
													<?php foreach ( give_get_payment_statuses() as $key => $status ) : ?>
														<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $payment->status, $key, true ); ?>><?php echo esc_html( $status ); ?></option>
													<?php endforeach; ?>
												</select>
												<span class="give-donation-status status-<?php echo sanitize_title( $payment->status ); ?>"><span class="give-donation-status-icon"></span></span>
											</p>
										</div>

										<div class="give-admin-box-inside">
											<?php $date_format = give_date_format(); ?>
											<p>
												<label for="give-payment-date" class="strong"><?php _e( 'Date:', 'give' ); ?></label>&nbsp;
												<input type="text" id="give-payment-date" name="give-payment-date" data-standard-date="<?php echo esc_attr( date( 'Y-m-d', $payment_date ) ); ?>" value="<?php echo esc_attr( date_i18n( $date_format, $payment_date ) ); ?>" autocomplete="off" class="medium-text give_datepicker" placeholder="<?php _e( 'Date', 'give' ); ?>"/>
											</p>
										</div>

										<div class="give-admin-box-inside">
											<p>
												<label for="give-payment-time-hour" class="strong"><?php _e( 'Time:', 'give' ); ?></label>&nbsp;
												<input type="number" step="1" max="24" id="give-payment-time-hour" name="give-payment-time-hour" value="<?php echo esc_attr( date_i18n( 'H', $payment_date ) ); ?>" class="small-text give-payment-time-hour"/>&nbsp;:&nbsp;
												<input type="number" step="1" max="59" id="give-payment-time-min" name="give-payment-time-min" value="<?php echo esc_attr( date( 'i', $payment_date ) ); ?>" class="small-text give-payment-time-min"/>
											</p>
										</div>

										<?php
										/**
										 * Fires in donation details page, in the sidebar update-payment metabox.
										 *
										 * Allows you to add new inner items.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'give_view_donation_details_update_inner', $payment_id );
										?>

										<div class="give-order-payment give-admin-box-inside">
											<p>
												<label for="give-payment-total" class="strong"><?php _e( 'Total Donation:', 'give' ); ?></label>&nbsp;
												<?php echo give_currency_symbol( $payment->currency ); ?>
												&nbsp;<input id="give-payment-total" name="give-payment-total" type="text" class="small-text give-price-field" value="<?php echo esc_attr( give_format_decimal( array( 'donation_id' => $payment_id ) ) ); ?>"/>
											</p>
										</div>

										<?php
										/**
										 * Fires in donation details page, after the sidebar update-donation metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'give_view_donation_details_totals_after', $payment_id );
										?>

									</div>
									<!-- /.give-admin-box -->

								</div>
								<!-- /.inside -->

								<div class="give-order-update-box give-admin-box">
									<?php
									/**
									 * Fires in donation details page, before the sidebar update-payment metabox actions buttons.
									 *
									 * @since 1.0
									 *
									 * @param int $payment_id Payment id.
									 */
									do_action( 'give_view_donation_details_update_before', $payment_id );
									?>

									<div id="major-publishing-actions">
										<div id="publishing-action">
											<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Donation', 'give' ); ?>"/>
											<?php
											if ( give_is_payment_complete( $payment_id ) ) {
												$url = add_query_arg(
													array(
														'give-action' => 'email_links',
														'purchase_id' => $payment_id,
													),
													admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $payment_id )
												);

												echo sprintf(
													'<a href="%1$s" id="give-resend-receipt" class="button-secondary right">%2$s</a>',
													esc_url( $url ),
													esc_html__( 'Resend Receipt', 'give' )
												);
											}
											?>
										</div>
										<div class="clear"></div>
									</div>
									<?php
									/**
									 * Fires in donation details page, after the sidebar update-payment metabox actions buttons.
									 *
									 * @since 1.0
									 *
									 * @param int $payment_id Payment id.
									 */
									do_action( 'give_view_donation_details_update_after', $payment_id );
									?>

								</div>
								<!-- /.give-order-update-box -->

							</div>
							<!-- /#give-order-data -->

							<div id="give-order-details" class="postbox give-order-data">

								<h3 class="hndle"><?php _e( 'Donation Meta', 'give' ); ?></h3>

								<div class="inside">
									<div class="give-admin-box">

										<?php
										/**
										 * Fires in donation details page, before the donation-meta metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'give_view_donation_details_payment_meta_before', $payment_id );

										$gateway = give_get_payment_gateway( $payment_id );
										if ( $gateway ) :
											?>
											<div class="give-order-gateway give-admin-box-inside">
												<p>
													<strong><?php _e( 'Gateway:', 'give' ); ?></strong>&nbsp;
													<?php echo give_get_gateway_admin_label( $gateway ); ?>
												</p>
											</div>
										<?php endif; ?>

										<div class="give-order-payment-key give-admin-box-inside">
											<p>
												<strong><?php _e( 'Key:', 'give' ); ?></strong>&nbsp;
												<?php echo give_get_payment_key( $payment_id ); ?>
											</p>
										</div>

										<div class="give-order-ip give-admin-box-inside">
											<p>
												<strong><?php _e( 'IP:', 'give' ); ?></strong>&nbsp;
												<?php echo esc_html( give_get_payment_user_ip( $payment_id ) ); ?>
											</p>
										</div>

										<?php
										// Display the transaction ID present.
										// The transaction ID is the charge ID from the gateway.
										// For instance, stripe "ch_BzvwYCchqOy5Nt".
										if ( $transaction_id != $payment_id ) : ?>
											<div class="give-order-tx-id give-admin-box-inside">
												<p>
													<strong><?php _e( 'Transaction ID:', 'give' ); ?> <span class="give-tooltip give-icon give-icon-question"  data-tooltip="<?php echo sprintf( esc_attr__( 'The transaction ID within %s.', 'give' ), $gateway); ?>"></span></strong>&nbsp;
													<?php echo apply_filters( "give_payment_details_transaction_id-{$gateway}", $transaction_id, $payment_id ); ?>
												</p>
											</div>
										<?php endif; ?>

										<?php
										/**
										 * Fires in donation details page, after the donation-meta metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'give_view_donation_details_payment_meta_after', $payment_id );
										?>

										<div class="give-admin-box-inside">
											<p><?php $purchase_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&donor=' . absint( give_get_payment_donor_id( $payment_id ) ) ); ?>
												<a href="<?php echo $purchase_url; ?>"><?php _e( 'View all donations for this donor &raquo;', 'give' ); ?></a>
											</p>
										</div>
										
									</div>
									<!-- /.column-container -->

								</div>
								<!-- /.inside -->

							</div>
							<!-- /#give-order-data -->

							<?php
							/**
							 * Fires in donation details page, after the sidebar.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_donation_details_sidebar_after', $payment_id );
							?>

						</div>
						<!-- /#side-sortables -->
					</div>
					<!-- /#postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">

						<div id="normal-sortables" class="meta-box-sortables ui-sortable">

							<?php
							/**
							 * Fires in donation details page, before the main area.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_donation_details_main_before', $payment_id );
							?>

							<?php $column_count = 'columns-3'; ?>
							<div id="give-donation-overview" class="postbox <?php echo $column_count; ?>">
								<h3 class="hndle"><?php _e( 'Donation Information', 'give' ); ?></h3>

								<div class="inside">

									<div class="column-container">
										<div class="column">
											<p>
												<strong><?php _e( 'Donation Form ID:', 'give' ); ?></strong><br>
												<?php
												if ( $payment->form_id ) :
													printf(
														'<a href="%1$s">%2$s</a>',
														admin_url( 'post.php?action=edit&post=' . $payment->form_id ),
														$payment->form_id
													);
												endif;
												?>
											</p>
											<p>
												<strong><?php esc_html_e( 'Donation Form Title:', 'give' ); ?></strong><br>
												<?php
												echo Give()->html->forms_dropdown(
													array(
														'selected' => $payment->form_id,
														'name' => 'give-payment-form-select',
														'id'   => 'give-payment-form-select',
														'chosen' => true,
														'placeholder' => '',
													)
												);
												?>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php _e( 'Donation Date:', 'give' ); ?></strong><br>
												<?php echo date_i18n( give_date_format(), $payment_date ); ?>
											</p>
											<p>
												<strong><?php _e( 'Donation Level:', 'give' ); ?></strong><br>
												<span class="give-donation-level">
													<?php
													$var_prices = give_has_variable_prices( $payment->form_id );
													if ( empty( $var_prices ) ) {
														_e( 'n/a', 'give' );
													} else {
														$prices_atts = array();
														if ( $variable_prices = give_get_variable_prices( $payment->form_id ) ) {
															foreach ( $variable_prices as $variable_price ) {
																$prices_atts[ $variable_price['_give_id']['level_id'] ] = give_format_amount( $variable_price['_give_amount'], array( 'sanitize' => false ) );
															}
														}
														// Variable price dropdown options.
														$variable_price_dropdown_option = array(
															'id'               => $payment->form_id,
															'name'             => 'give-variable-price',
															'chosen'           => true,
															'show_option_all'  => '',
															'show_option_none' => ( '' === $payment->price_id ? __( 'None', 'give' ) : '' ),
															'select_atts'      => 'data-prices=' . esc_attr( wp_json_encode( $prices_atts ) ),
															'selected'         => $payment->price_id,
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
												<?php echo give_donation_amount( $payment, true ); ?>
											</p>

											<?php if ( give_is_anonymous_donation_field_enabled( $payment->form_id ) ):  ?>
												<div>
													<strong><?php esc_html_e( 'Anonymous Donation:', 'give' ); ?></strong>
													<ul class="give-radio-inline">
														<li>
															<label>
																<input
																	name="give_anonymous_donation"
																	value="1"
																	type="radio"
																	<?php checked( 1, absint( give_get_meta( $payment_id, '_give_anonymous_donation', true ) ) ) ?>
																><?php _e( 'Yes', 'give' ); ?>
															</label>
														</li>
														<li>
															<label>
																<input
																	name="give_anonymous_donation"
																	value="0"
																	type="radio"
																	<?php checked( 0, absint( give_get_meta( $payment_id, '_give_anonymous_donation', true ) ) ) ?>
																><?php _e( 'No', 'give' ); ?>
															</label>
														</li>
													</ul>
												</div>
											<?php endif; ?>
											<p>
												<?php
												/**
												 * Fires in donation details page, in the donation-information metabox, before the head elements.
												 *
												 * Allows you to add new TH elements at the beginning.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'give_donation_details_thead_before', $payment_id );


												/**
												 * Fires in donation details page, in the donation-information metabox, after the head elements.
												 *
												 * Allows you to add new TH elements at the end.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'give_donation_details_thead_after', $payment_id );

												/**
												 * Fires in donation details page, in the donation-information metabox, before the body elements.
												 *
												 * Allows you to add new TD elements at the beginning.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'give_donation_details_tbody_before', $payment_id );

												/**
												 * Fires in donation details page, in the donation-information metabox, after the body elements.
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
							 * Fires on the donation details page.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_donation_details_donor_detail_before', $payment_id );
							?>

							<div id="give-donor-details" class="postbox">
								<h3 class="hndle"><?php _e( 'Donor Details', 'give' ); ?></h3>

								<div class="inside">

									<?php $donor = new Give_Donor( $donor_id ); ?>

									<div class="column-container donor-info">
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Donor ID:', 'give' ); ?></strong><br>
												<?php
												if ( ! empty( $donor->id ) ) {
													printf(
														'<a href="%1$s">%2$s</a>',
														esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ) ),
														intval( $donor->id )
													);
												}
												?>
												<span>(<a href="#new" class="give-payment-new-donor"><?php esc_html_e( 'Create New Donor', 'give' ); ?></a>)</span>
											</p>
											<p>
												<strong><?php esc_html_e( 'Donor Since:', 'give' ); ?></strong><br>
												<?php echo date_i18n( give_date_format(), strtotime( $donor->date_created ) ) ?>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Donor Name:', 'give' ); ?></strong><br>
												<?php
												$donor_billing_name = give_get_donor_name_by( $payment_id, 'donation' );
												$donor_name         = give_get_donor_name_by( $donor_id, 'donor' );

												// Check whether the donor name and WP_User name is same or not.
												if ( $donor_billing_name !== $donor_name ) {
													echo sprintf(
														'%1$s (<a href="%2$s" target="_blank">%3$s</a>)',
														esc_html( $donor_billing_name ),
														esc_url( admin_url( "edit.php?post_type=give_forms&page=give-donors&view=overview&id={$donor_id}" ) ),
														esc_html( $donor_name )
													);
												} else {
													echo esc_html( $donor_name );
												}
												?>
											</p>
											<p>
												<strong><?php esc_html_e( 'Donor Email:', 'give' ); ?></strong><br>
												<?php
												// Show Donor donation email first and Primary email on parenthesis if not match both email.
												echo hash_equals( $donor->email, $payment->email )
													? $payment->email
													: sprintf(
														'%1$s (<a href="%2$s" target="_blank">%3$s</a>)',
														$payment->email,
														esc_url( admin_url( "edit.php?post_type=give_forms&page=give-donors&view=overview&id={$donor_id}" ) ),
														$donor->email
													);
												?>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Change Donor:', 'give' ); ?></strong><br>
												<?php
												echo Give()->html->donor_dropdown(
													array(
														'selected' => $donor->id,
														'name' => 'donor-id',
													)
												);
												?>
											</p>
											<p>
												<?php if ( ! empty( $company_name ) ) {
													?>
													<strong><?php esc_html_e( 'Company Name:', 'give' ); ?></strong><br>
													<?php
													echo $company_name;
												} ?>
											</p>
										</div>
									</div>

									<div class="column-container new-donor" style="display: none">
										<div class="column">
											<p>
												<label for="give-new-donor-first-name"><?php _e( 'New Donor First Name:', 'give' ); ?></label>
												<input id="give-new-donor-first-name" type="text" name="give-new-donor-first-name" value="" class="medium-text"/>
											</p>
										</div>
										<div class="column">
											<p>
												<label for="give-new-donor-last-name"><?php _e( 'New Donor Last Name:', 'give' ); ?></label>
												<input id="give-new-donor-last-name" type="text" name="give-new-donor-last-name" value="" class="medium-text"/>
											</p>
										</div>
										<div class="column">
											<p>
												<label for="give-new-donor-email"><?php _e( 'New Donor Email:', 'give' ); ?></label>
												<input id="give-new-donor-email" type="email" name="give-new-donor-email" value="" class="medium-text"/>
											</p>
										</div>
										<div class="column">
											<p>
												<input type="hidden" name="give-current-donor" value="<?php echo $donor->id; ?>"/>
												<input type="hidden" id="give-new-donor" name="give-new-donor" value="0"/>
												<a href="#cancel" class="give-payment-new-donor-cancel give-delete"><?php _e( 'Cancel', 'give' ); ?></a>
												<br>
												<em><?php _e( 'Click "Save Donation" to create new donor.', 'give' ); ?></em>
											</p>
										</div>
									</div>
									<?php
									/**
									 * Fires on the donation details page, in the donor-details metabox.
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
									 * Fires on the donation details page, in the donor-details metabox.
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
							 * Fires on the donation details page, before the billing metabox.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_donation_details_billing_before', $payment_id );
							?>

							<div id="give-billing-details" class="postbox">
								<h3 class="hndle"><?php _e( 'Billing Address', 'give' ); ?></h3>

								<div class="inside">

									<div id="give-order-address">

										<div class="order-data-address">
											<div class="data column-container">

												<?php
												$address['country'] = ( ! empty( $address['country'] ) ? $address['country'] : give_get_country() );

												$address['state'] = ( ! empty( $address['state'] ) ? $address['state'] : '' );

												// Get the country list that does not have any states init.
												$no_states_country = give_no_states_country_list();
												?>

												<div class="row">
													<div id="give-order-address-country-wrap">
														<label class="order-data-address-line"><?php _e( 'Country:', 'give' ); ?></label>
														<?php
														echo Give()->html->select(
															array(
																'options'          => give_get_country_list(),
																'name'             => 'give-payment-address[0][country]',
																'selected'         => $address['country'],
																'show_option_all'  => false,
																'show_option_none' => false,
																'chosen'           => true,
																'placeholder'      => esc_attr__( 'Select a country', 'give' ),
																'data'             => array( 'search-type' => 'no_ajax' ),
																'autocomplete'     => 'country',
															)
														);
														?>
													</div>
												</div>

												<div class="row">
													<div class="give-wrap-address-line1">
														<label for="give-payment-address-line1" class="order-data-address"><?php _e( 'Address 1:', 'give' ); ?></label>
														<input id="give-payment-address-line1" type="text" name="give-payment-address[0][line1]" value="<?php echo esc_attr( $address['line1'] ); ?>" class="medium-text"/>
													</div>
												</div>

												<div class="row">
													<div class="give-wrap-address-line2">
														<label for="give-payment-address-line2" class="order-data-address-line"><?php _e( 'Address 2:', 'give' ); ?></label>
														<input id="give-payment-address-line2" type="text" name="give-payment-address[0][line2]" value="<?php echo esc_attr( $address['line2'] ); ?>" class="medium-text"/>
													</div>
												</div>

												<div class="row">
													<div class="give-wrap-address-city">
														<label for="give-payment-address-city" class="order-data-address-line"><?php esc_html_e( 'City:', 'give' ); ?></label>
														<input id="give-payment-address-city" type="text" name="give-payment-address[0][city]" value="<?php echo esc_attr( $address['city'] ); ?>" class="medium-text"/>
													</div>
												</div>

												<?php
												$state_exists = ( ! empty( $address['country'] ) && array_key_exists( $address['country'], $no_states_country ) ? true : false );
												?>
												<div class="row">
													<div class="<?php echo( ! empty( $state_exists ) ? 'column-full' : 'column' ); ?> give-column give-column-state">
														<div id="give-order-address-state-wrap" class="<?php echo( ! empty( $state_exists ) ? 'give-hidden' : '' ); ?>">
															<label for="give-payment-address-state" class="order-data-address-line"><?php esc_html_e( 'State / Province / County:', 'give' ); ?></label>
															<?php
															$states = give_get_states( $address['country'] );
															if ( ! empty( $states ) ) {
																echo Give()->html->select(
																	array(
																		'options'          => $states,
																		'name'             => 'give-payment-address[0][state]',
																		'selected'         => $address['state'],
																		'show_option_all'  => false,
																		'show_option_none' => false,
																		'chosen'           => true,
																		'placeholder'      => esc_attr__( 'Select a state', 'give' ),
																		'data'             => array( 'search-type' => 'no_ajax' ),
																		'autocomplete' => 'address-level1',
																	)
																);
															} else {
																?>
																<input id="give-payment-address-state" type="text" name="give-payment-address[0][state]" autocomplete="address-line1" value="<?php echo esc_attr( $address['state'] ); ?>" class="medium-text"/>
																<?php
															}
															?>
														</div>
													</div>

													<div class="<?php echo( ! empty( $state_exists ) ? 'column-full' : 'column' ); ?> give-column give-column-zip">
														<div class="give-wrap-address-zip">
															<label for="give-payment-address-zip" class="order-data-address-line"><?php _e( 'Zip / Postal Code:', 'give' ); ?></label>
															<input id="give-payment-address-zip" type="text" name="give-payment-address[0][zip]" value="<?php echo esc_attr( $address['zip'] ); ?>" class="medium-text"/>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- /#give-order-address -->

									<?php
									/**
									 * Fires in donation details page, in the billing metabox, after all the fields.
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
							 * Fires on the donation details page, after the billing metabox.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_donation_details_billing_after', $payment_id );
							?>

							<div id="give-payment-notes" class="postbox">
								<h3 class="hndle"><?php _e( 'Donation Notes', 'give' ); ?></h3>

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

										echo '<p class="give-no-payment-notes"' . $no_notes_display . '>' . esc_html__( 'No donation notes.', 'give' ) . '</p>';
										?>
									</div>
									<textarea name="give-payment-note" id="give-payment-note" class="large-text"></textarea>

									<div class="give-clearfix">
										<p>
											<label for="donation_note_type" class="screen-reader-text"><?php _e( 'Note type', 'give' ); ?></label>
											<select name="donation_note_type" id="donation_note_type">
												<option value=""><?php _e( 'Private note', 'give' ); ?></option>
												<option value="donor"><?php _e( 'Note to donor', 'give' ); ?></option>
											</select>
											<button id="give-add-payment-note" class="button button-secondary button-small" data-payment-id="<?php echo absint( $payment_id ); ?>"><?php _e( 'Add Note', 'give' ); ?></button>
										</p>
									</div>

								</div>
								<!-- /.inside -->
							</div>
							<!-- /#give-payment-notes -->

							<?php
							/**
							 * Fires on the donation details page, after the main area.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_donation_details_main_after', $payment_id );
							?>

							<?php if ( give_is_donor_comment_field_enabled( $payment->form_id ) ) : ?>
								<div id="give-payment-donor-comment" class="postbox">
									<h3 class="hndle"><?php _e( 'Donor Comment', 'give' ); ?></h3>

									<div class="inside">
										<div id="give-payment-donor-comment-inner">
											<p>
												<?php
												$donor_comment = give_get_donor_donation_comment( $payment_id, $payment->donor_id );

												echo sprintf(
													'<input type="hidden" name="give_comment_id" value="%s">',
													$donor_comment instanceof WP_Comment // Backward compatibility.
														|| $donor_comment instanceof stdClass
															? $donor_comment->comment_ID : 0
												);

												echo sprintf(
													'<textarea name="give_comment" id="give_comment" placeholder="%s" class="large-text">%s</textarea>',
													__( 'Add a comment', 'give' ),
													$donor_comment instanceof WP_Comment // Backward compatibility.
													|| $donor_comment instanceof stdClass
														? $donor_comment->comment_content : ''
												);
												?>
											</p>
										</div>

									</div>
									<!-- /.inside -->
								</div>
							<?php endif; ?>
							<!-- /#give-payment-notes -->

							<?php
							/**
							 * Fires on the donation details page, after the main area.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'give_view_donation_details_main_after', $payment_id );
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
		 * Fires in donation details page, in the form after the order details.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'give_view_donation_details_form_bottom', $payment_id );

		wp_nonce_field( 'give_update_payment_details_nonce' );
		?>
		<input type="hidden" name="give_payment_id" value="<?php echo esc_attr( $payment_id ); ?>"/>
		<input type="hidden" name="give_action" value="update_payment_details"/>
	</form>
	<?php
	/**
	 * Fires in donation details page, after the order form.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id Payment id.
	 */
	do_action( 'give_view_donation_details_after', $payment_id );
	?>
</div><!-- /.wrap -->
