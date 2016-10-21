<?php
/**
 * Customer (Donors)
 *
 * @package     Give
 * @subpackage  Admin/Customers
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customers Page
 *
 * Renders the customers page contents.
 *
 * @since  1.0
 * @return void
 */
function give_customers_page() {
	$default_views  = give_customer_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'customers';
	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[ $requested_view ] ) ) {
		give_render_customer_view( $requested_view, $default_views );
	} else {
		give_customers_list();
	}
}

/**
 * Register the views for customer management
 *
 * @since  1.0
 * @return array Array of views and their callbacks
 */
function give_customer_views() {

	$views = array();

	return apply_filters( 'give_customer_views', $views );

}

/**
 * Register the tabs for customer management
 *
 * @since  1.0
 * @return array Array of tabs for the customer
 */
function give_customer_tabs() {

	$tabs = array();

	return apply_filters( 'give_customer_tabs', $tabs );

}

/**
 * List table of customers
 *
 * @since  1.0
 * @return void
 */
function give_customers_list() {
	include( dirname( __FILE__ ) . '/class-customer-table.php' );

	$customers_table = new Give_Customer_Reports_Table();
	$customers_table->prepare_items();
	?>
	<div class="wrap">
		<h1><?php echo get_admin_page_title(); ?></h1>
		<?php
		/**
		 * Fires in donors screen, above the table.
		 *
		 * @since 1.0
		 */
		do_action( 'give_donors_table_top' );
		?>
		<form id="give-donors-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors' ); ?>">
			<?php
			$customers_table->search_box( esc_html__( 'Search Donors', 'give' ), 'give-donors' );
			$customers_table->display();
			?>
			<input type="hidden" name="post_type" value="give_forms" />
			<input type="hidden" name="page" value="give-donors" />
			<input type="hidden" name="view" value="customers" />
		</form>
		<?php
		/**
		 * Fires in donors screen, below the table.
		 *
		 * @since 1.0
		 */
		do_action( 'give_donors_table_bottom' );
		?>
	</div>
	<?php
}

/**
 * Renders the customer view wrapper
 *
 * @since  1.0
 *
 * @param  string $view      The View being requested
 * @param  array  $callbacks The Registered views and their callback functions
 *
 * @return void
 */
function give_render_customer_view( $view, $callbacks ) {

	$render = true;

	$customer_view_role = apply_filters( 'give_view_customers_role', 'view_give_reports' );

	if ( ! current_user_can( $customer_view_role ) ) {
		give_set_error( 'give-no-access', esc_html__( 'You are not permitted to view this data.', 'give' ) );
		$render = false;
	}
 
	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		give_set_error( 'give-invalid_customer', esc_html__( 'Invalid Donor ID.', 'give' ) );
		$render = false;
	}

	$customer_id = (int) $_GET['id'];
	$customer    = new Give_Customer( $customer_id );

	if ( empty( $customer->id ) ) {
		give_set_error( 'give-invalid_customer', esc_html__( 'Invalid Donor ID.', 'give' ) );
		$render = false;
	}

	$customer_tabs = give_customer_tabs();
	?>

	<div class='wrap'>

		<?php if ( give_get_errors() ) : ?>
			<div class="error settings-error">
				<?php give_print_errors( 0 ); ?>
			</div>
		<?php endif; ?>

		<h1 class="screen-reader-text"><?php esc_html_e( 'Donor', 'give' ); ?></h1>

		<?php if ( $customer && $render ) : ?>

			<h2 class="nav-tab-wrapper">
			<?php
			foreach ( $customer_tabs as $key => $tab ) :
				$active = $key === $view ? true : false;
				$class = $active ? 'nav-tab nav-tab-active' : 'nav-tab';
				printf(
					'<a href="%1$s" class="%2$s"><span class="dashicons %3$s"></span>%4$s</a>'."\n",
					esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=' . $key . '&id=' . $customer->id ) ),
					esc_attr( $class ),
					sanitize_html_class( $tab['dashicon'] ),
					esc_html( $tab['title'] )
				);
			endforeach;
			?>
			</h2>

			<div id="give-customer-card-wrapper">
				<?php $callbacks[ $view ]( $customer ) ?>
			</div>

		<?php endif; ?>

	</div>
	<?php

}


/**
 * View a customer
 *
 * @since  1.0
 *
 * @param  object $customer The Customer object being displayed.
 *
 * @return void
 */
function give_customers_view( $customer ) {

	$customer_edit_role = apply_filters( 'give_edit_customers_role', 'edit_give_payments' );

	/**
	 * Fires in donor profile screen, above the donor card.
	 *
	 * @since 1.0
	 *
	 * @param object $customer The customer object being displayed.
	 */
	do_action( 'give_donor_card_top', $customer );
	?>

	<div id="donor-summary" class="info-wrapper customer-section postbox">

		<form id="edit-customer-info" method="post" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $customer->id ); ?>">

			<div class="customer-info">

				<div class="donor-bio-header clearfix">

					<div class="avatar-wrap left" id="customer-avatar">
						<?php echo get_avatar( $customer->email ); ?>
					</div>

					<div id="customer-name-wrap" class="left">
						<span class="customer-id">#<?php echo $customer->id; ?></span>
						<span class="customer-name info-item edit-item"><input size="15" data-key="name" name="customerinfo[name]" type="text" value="<?php echo esc_attr( $customer->name ); ?>" placeholder="<?php esc_attr_e( 'Donor Name', 'give' ); ?>" /></span>
						<span class="customer-name info-item editable"><span data-key="name"><?php echo $customer->name; ?></span></span>
					</div>
					<p class="customer-since info-item">
						<?php esc_html_e( 'Donor since', 'give' ); ?>
						<?php echo date_i18n( give_date_format(), strtotime( $customer->date_created ) ) ?>
					</p>
					<?php if ( current_user_can( $customer_edit_role ) ): ?>
						<a href="#" id="edit-customer" class="button info-item editable customer-edit-link"><?php esc_html_e( 'Edit Donor', 'give' ); ?></a>
					<?php endif; ?>
				</div>
				<!-- /donor-bio-header -->

				<div class="customer-main-wrapper">

					<table class="widefat">
						<tbody>
						<tr class="alternate">
							<th scope="col"><label for="tablecell"><?php esc_html_e( 'User ID:', 'give' ); ?></label></th>
							<td class="row-title">
								<span class="customer-user-id info-item edit-item">
									<?php

									$user_id   = $customer->user_id > 0 ? $customer->user_id : '';
									$data_atts = array(
										'key'     => 'user_login',
										'exclude' => $user_id
									);
									$user_args = array(
										'name'  => 'customerinfo[user_login]',
										'class' => 'give-user-dropdown',
										'data'  => $data_atts,
									);

									if ( ! empty( $user_id ) ) {
										$userdata           = get_userdata( $user_id );
										$user_args['value'] = $userdata->user_login;
									}

									echo Give()->html->ajax_user_search( $user_args );
									?>
									<input type="hidden" name="customerinfo[user_id]" data-key="user_id" value="<?php echo $customer->user_id; ?>" />
								</span>
			
								<span class="customer-user-id info-item editable">
									<?php if ( intval( $customer->user_id ) > 0 ) { ?>
										<span data-key="user_id"><?php echo $customer->user_id; ?></span>
									<?php } else { ?>
										<span data-key="user_id"><?php esc_html_e( 'None', 'give' ); ?></span>
									<?php } ?>
									<?php if ( current_user_can( $customer_edit_role ) && intval( $customer->user_id ) > 0 ) { ?>
										<span class="disconnect-user"> - <a id="disconnect-customer" href="#disconnect" aria-label="<?php esc_attr_e( 'Disconnects the current user ID from this customer record.', 'give' ); ?>"><?php esc_html_e( 'Disconnect User', 'give' ); ?></a></span>
									<?php } ?>
								</span>
							</td>
						</tr>
						<?php if ( isset( $customer->user_id ) && $customer->user_id > 0 ) : ?>

							<tr>
								<th scope="col"><?php esc_html_e( 'Address:', 'give' ); ?></th>
								<td class="row-title">

									<div class="customer-address-wrapper">

										<?php
										$address  = get_user_meta( $customer->user_id, '_give_user_address', true );
										$defaults = array(
											'line1'   => '',
											'line2'   => '',
											'city'    => '',
											'state'   => '',
											'country' => '',
											'zip'     => ''
										);

										$address = wp_parse_args( $address, $defaults );
										?>

										<?php if ( ! empty( $address ) ) { ?>
											<span class="customer-address info-item editable">
												<span class="info-item" data-key="line1"><?php echo $address['line1']; ?></span>
												<span class="info-item" data-key="line2"><?php echo $address['line2']; ?></span>
												<span class="info-item" data-key="city"><?php echo $address['city']; ?></span>
												<span class="info-item" data-key="state"><?php echo $address['state']; ?></span>
												<span class="info-item" data-key="country"><?php echo $address['country']; ?></span>
												<span class="info-item" data-key="zip"><?php echo $address['zip']; ?></span>
											</span>
										<?php } ?>
										<span class="customer-address info-item edit-item">
											<input class="info-item" type="text" data-key="line1" name="customerinfo[line1]" placeholder="<?php esc_attr_e( 'Address 1', 'give' ); ?>" value="<?php echo $address['line1']; ?>" />
											<input class="info-item" type="text" data-key="line2" name="customerinfo[line2]" placeholder="<?php esc_attr_e( 'Address 2', 'give' ); ?>" value="<?php echo $address['line2']; ?>" />
											<input class="info-item" type="text" data-key="city" name="customerinfo[city]" placeholder="<?php esc_attr_e( 'City', 'give' ); ?>" value="<?php echo $address['city']; ?>" />
											<select data-key="country" name="customerinfo[country]" id="billing_country" class="billing_country give-select edit-item">
												<?php

												$selected_country = $address['country'];

												$countries = give_get_country_list();
												foreach ( $countries as $country_code => $country ) {
													echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
												}
												?>
											</select>
											<?php
											$selected_state = give_get_state();
											$states         = give_get_states( $selected_country );

											$selected_state = isset( $address['state'] ) ? $address['state'] : $selected_state;

											if ( ! empty( $states ) ) {
												?>
												<select data-key="state" name="customerinfo[state]" id="card_state" class="card_state give-select info-item">
													<?php
													foreach ( $states as $state_code => $state ) {
														echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
													}
													?>
												</select>
												<?php
											} else {
												?>
												<input type="text" size="6" data-key="state" name="customerinfo[state]" id="card_state" class="card_state give-input info-item" placeholder="<?php esc_attr_e( 'State / Province', 'give' ); ?>" />
												<?php
											}
											?>
											<input class="info-item" type="text" data-key="zip" name="customerinfo[zip]" placeholder="<?php esc_attr_e( 'Zip / Postal Code', 'give' ); ?>" value="<?php echo $address['zip']; ?>" />
										</span>

									</div>
								</td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>

				</div>

			</div>

			<span id="customer-edit-actions" class="edit-item">
				<input type="hidden" data-key="id" name="customerinfo[id]" value="<?php echo $customer->id; ?>" />
				<?php wp_nonce_field( 'edit-customer', '_wpnonce', false, true ); ?>
				<input type="hidden" name="give_action" value="edit-customer" />
				<input type="submit" id="give-edit-customer-save" class="button-secondary" value="<?php esc_attr_e( 'Update Donor', 'give' ); ?>" />
				<a id="give-edit-customer-cancel" href="" class="delete"><?php esc_html_e( 'Cancel', 'give' ); ?></a>
			</span>

		</form>

	</div>

	<?php
	/**
	 * Fires in donor profile screen, above the stats list.
	 *
	 * @since 1.0
	 *
	 * @param object $customer The customer object being displayed.
	 */
	do_action( 'give_donor_before_stats', $customer );
	?>

	<div id="customer-stats-wrapper" class="customer-section postbox clear">
		<ul>
			<li>
				<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&user=' . urlencode( $customer->email ) ); ?>">
					<span class="dashicons dashicons-heart"></span>
					<?php
					//Completed Donations
					$completed_donations_text = sprintf( _n( '%d Completed Donation', '%d Completed Donations', $customer->purchase_count, 'give' ), $customer->purchase_count );
					echo apply_filters( 'give_donor_completed_donations', $completed_donations_text, $customer );
					?>
				</a>
			</li>
			<li>
				<span class="dashicons dashicons-chart-area"></span>
				<?php echo give_currency_filter( give_format_amount( $customer->purchase_value ) ); ?> <?php esc_html_e( 'Lifetime Donations', 'give' ); ?>
			</li>
			<?php
			/**
			 * Fires in donor profile screen, in the stats list.
			 *
			 * Allows you to add more list items to the stats list.
			 *
			 * @since 1.0
			 *
			 * @param object $customer The customer object being displayed.
			 */
			do_action( 'give_donor_stats_list', $customer );
			?>
		</ul>
	</div>

	<?php
	/**
	 * Fires in donor profile screen, above the tables wrapper.
	 *
	 * @since 1.0
	 *
	 * @param object $customer The customer object being displayed.
	 */
	do_action( 'give_donor_before_tables_wrapper', $customer );
	?>

	<div id="customer-tables-wrapper" class="customer-section">

		<?php
		/**
		 * Fires in donor profile screen, above the tables.
		 *
		 * @since 1.0
		 *
		 * @param object $customer The customer object being displayed.
		 */
		do_action( 'give_donor_before_tables', $customer );
		?>

		<h3><?php _e( 'Donor Emails', 'give' ); ?></h3>

		<table class="wp-list-table widefat striped emails">
			<thead>
				<tr>
					<th><?php _e( 'Email', 'give' ); ?></th>
					<th><?php _e( 'Actions', 'give' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php if ( ! empty( $customer->emails ) ) { ?>

					<?php foreach ( $customer->emails as $key => $email ) : ?>
						<tr data-key="<?php echo $key; ?>">
							<td>
								<?php echo $email; ?>
								<?php if ( 'primary' === $key ) : ?>
									<span class="dashicons dashicons-star-filled primary-email-icon"></span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( 'primary' !== $key ) : ?>
									<?php
									$base_url    = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $customer->id );
									$promote_url = wp_nonce_url( add_query_arg( array( 'email' => rawurlencode( $email ), 'give_action' => 'set_donor_primary_email'), $base_url ), 'give-set-donor-primary-email' );
									$remove_url  = wp_nonce_url( add_query_arg( array( 'email' => rawurlencode( $email ), 'give_action' => 'remove_donor_email' ), $base_url ), 'give-remove-donor-email'      );
									?>
									<a href="<?php echo $promote_url; ?>"><?php _e( 'Make Primary', 'give' ); ?></a>
									&nbsp;|&nbsp;
									<a href="<?php echo $remove_url; ?>" class="delete"><?php _e( 'Remove', 'give' ); ?></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>

					<tr class="add-customer-email-row">
						<td colspan="2" class="add-customer-email-td">
							<div class="add-customer-email-wrapper">
								<input type="hidden" name="customer-id" value="<?php echo $customer->id; ?>" />
								<?php wp_nonce_field( 'give_add_donor_email', 'add_email_nonce', false, true ); ?>
								<input type="email" name="additional-email" value="" placeholder="<?php _e( 'Email Address', 'give' ); ?>" />&nbsp;
								<input type="checkbox" name="make-additional-primary" value="1" id="make-additional-primary" />&nbsp;<label for="make-additional-primary"><?php _e( 'Make Primary', 'give' ); ?></label>
								<button class="button-secondary give-add-customer-email" id="add-customer-email"><?php _e( 'Add Email', 'give' ); ?></button>
								<span class="spinner"></span>
							</div>
							<div class="notice-wrap"></div>
						</td>
					</tr>
				<?php } else { ?>
					<tr><td colspan="2"><?php _e( 'No Emails Found', 'easy-digital-downloads' ); ?></td></tr>
				<?php } ?>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Recent Donations', 'give' ); ?></h3>
		<?php
		$payment_ids = explode( ',', $customer->payment_ids );
		$payments    = give_get_payments( array( 'post__in' => $payment_ids ) );
		$payments    = array_slice( $payments, 0, 10 );
		?>
		<table class="wp-list-table widefat striped payments">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'ID', 'give' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Amount', 'give' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'give' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'give' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Actions', 'give' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( ! empty( $payments ) ) { ?>
				<?php foreach ( $payments as $payment ) : ?>
					<tr>
						<td><?php echo $payment->ID; ?></td>
						<td><?php echo give_payment_amount( $payment->ID ); ?></td>
						<td><?php echo date_i18n( give_date_format(), strtotime( $payment->post_date ) ); ?></td>
						<td><?php echo give_get_payment_status( $payment, true ); ?></td>
						<td>
							<?php
							printf(
								'<a href="%1$s" aria-label="%2$s">%3$s</a>',
								admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&id=' . $payment->ID ),
								sprintf(
									/* translators: %s: Donation ID */
									esc_attr__( 'View Donation %s.', 'give' ),
									$payment->ID
								),
								esc_html__( 'View Donation', 'give' )
							);
							?>

							<?php
							/**
							 * Fires in donor profile screen, in the recent donations tables action links.
							 *
							 * Allows you to add more action links for each donation, after the 'View Donation' action link.
							 *
							 * @since 1.0
							 *
							 * @param object $customer The customer object being displayed.
							 * @param object $payment  The payment object being displayed.
							 */
							do_action( 'give_donor_recent_purchases_actions', $customer, $payment );
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php } else { ?>
				<tr>
					<td colspan="5"><?php esc_html_e( 'No donations found.', 'give' ); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Completed Forms', 'give' ); ?></h3>
		<?php
		$donations = give_get_users_completed_donations( $customer->email );
		?>
		<table class="wp-list-table widefat striped donations">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Form', 'give' ); ?></th>
				<th scope="col" width="120px"><?php esc_html_e( 'Actions', 'give' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( ! empty( $donations ) ) { ?>
				<?php foreach ( $donations as $donation ) : ?>
					<tr>
						<td><?php echo $donation->post_title; ?></td>
						<td>
							<?php
							printf(
								'<a href="%1$s" aria-label="%2$s">%3$s</a>',
								esc_url( admin_url( 'post.php?action=edit&post=' . $donation->ID ) ),
								sprintf(
									/* translators: %s: form name */
									esc_attr__( 'View Form %s.', 'give' ),
									$donation->post_title
								),
								esc_html__( 'View Form', 'give' )
							);
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php } else { ?>
				<tr>
					<td colspan="2"><?php esc_html_e( 'No completed donations found.', 'give' ); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>

		<?php
		/**
		 * Fires in donor profile screen, below the tables.
		 *
		 * @since 1.0
		 *
		 * @param object $customer The customer object being displayed.
		 */
		do_action( 'give_donor_after_tables', $customer );
		?>

	</div>

	<?php
	/**
	 * Fires in donor profile screen, below the donor card.
	 *
	 * @since 1.0
	 *
	 * @param object $customer The customer object being displayed.
	 */
	do_action( 'give_donor_card_bottom', $customer );

}

/**
 * View the notes of a customer
 *
 * @since  1.0
 *
 * @param  object $customer The customer object being displayed.
 *
 * @return void
 */
function give_customer_notes_view( $customer ) {

	$paged          = isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$paged          = absint( $paged );
	$note_count     = $customer->get_notes_count();
	$per_page       = apply_filters( 'give_customer_notes_per_page', 20 );
	$total_pages    = ceil( $note_count / $per_page );
	$customer_notes = $customer->get_notes( $per_page, $paged );
	?>

	<div id="customer-notes-wrapper">
		<div class="customer-notes-header">
			<?php echo get_avatar( $customer->email, 30 ); ?> <span><?php echo $customer->name; ?></span>
		</div>
		<h3><?php esc_html_e( 'Notes', 'give' ); ?></h3>

		<?php if ( 1 == $paged ) : ?>
			<div style="display: block; margin-bottom: 55px;">
				<form id="give-add-customer-note" method="post" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=notes&id=' . $customer->id ); ?>">
					<textarea id="customer-note" name="customer_note" class="customer-note-input" rows="10"></textarea>
					<br />
					<input type="hidden" id="customer-id" name="customer_id" value="<?php echo $customer->id; ?>" />
					<input type="hidden" name="give_action" value="add-customer-note" />
					<?php wp_nonce_field( 'add-customer-note', 'add_customer_note_nonce', true, true ); ?>
					<input id="add-customer-note" class="right button-primary" type="submit" value="Add Note" />
				</form>
			</div>
		<?php endif; ?>

		<?php
		$pagination_args = array(
			'base'     => '%_%',
			'format'   => '?paged=%#%',
			'total'    => $total_pages,
			'current'  => $paged,
			'show_all' => true
		);

		echo paginate_links( $pagination_args );
		?>

		<div id="give-customer-notes" class="postbox">
			<?php if ( count( $customer_notes ) > 0 ) { ?>
				<?php foreach ( $customer_notes as $key => $note ) : ?>
					<div class="customer-note-wrapper dashboard-comment-wrap comment-item">
					<span class="note-content-wrap">
						<?php echo stripslashes( $note ); ?>
					</span>
					</div>
				<?php endforeach; ?>
			<?php } else { ?>
				<div class="give-no-customer-notes">
					<?php esc_html_e( 'No donor notes found.', 'give' ); ?>
				</div>
			<?php } ?>
		</div>

		<?php echo paginate_links( $pagination_args ); ?>

	</div>

	<?php
}

/**
 * Thw customer delete view
 *
 * @since  1.0
 *
 * @param  object $customer The customer object being displayed.
 *
 * @return void
 */
function give_customers_delete_view( $customer ) {

	$customer_edit_role = apply_filters( 'give_edit_customers_role', 'edit_give_payments' );

	/**
	 * Fires in donor delete screen, above the content.
	 *
	 * @since 1.0
	 *
	 * @param object $customer The customer object being displayed.
	 */
	do_action( 'give_customer_delete_top', $customer );
	?>

	<div class="info-wrapper customer-section">

		<form id="delete-customer" method="post" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=delete&id=' . $customer->id ); ?>">

			<div class="customer-notes-header">
				<?php echo get_avatar( $customer->email, 30 ); ?> <span><?php echo $customer->name; ?></span>
			</div>


			<div class="customer-info delete-customer">

				<span class="delete-customer-options">
					<p>
						<?php echo Give()->html->checkbox( array( 'name' => 'give-customer-delete-confirm' ) ); ?>
						<label for="give-customer-delete-confirm"><?php esc_html_e( 'Are you sure you want to delete this donor?', 'give' ); ?></label>
					</p>

					<p>
						<?php echo Give()->html->checkbox( array(
							'name'    => 'give-customer-delete-records',
							'options' => array( 'disabled' => true )
						) ); ?>
						<label for="give-customer-delete-records"><?php esc_html_e( 'Delete all associated donations and records?', 'give' ); ?></label>
					</p>

					<?php
					/**
					 * Fires in donor delete screen, bellow the delete inputs.
					 *
					 * Allows you to add custom delete inputs.
					 *
					 * @since 1.0
					 *
					 * @param object $customer The customer object being displayed.
					 */
					do_action( 'give_customer_delete_inputs', $customer );
					?>
				</span>

				<span id="customer-edit-actions">
					<input type="hidden" name="customer_id" value="<?php echo $customer->id; ?>" />
					<?php wp_nonce_field( 'delete-customer', '_wpnonce', false, true ); ?>
					<input type="hidden" name="give_action" value="delete-customer" />
					<input type="submit" disabled="disabled" id="give-delete-customer" class="button-primary" value="<?php esc_attr_e( 'Delete Donor', 'give' ); ?>" />
					<a id="give-delete-customer-cancel" href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $customer->id ); ?>" class="delete"><?php esc_html_e( 'Cancel', 'give' ); ?></a>
				</span>

			</div>

		</form>
	</div>

	<?php
	/**
	 * Fires in donor delete screen, bellow the content.
	 *
	 * @since 1.0
	 *
	 * @param object $customer The customer object being displayed.
	 */
	do_action( 'give_customer_delete_bottom', $customer );
}
