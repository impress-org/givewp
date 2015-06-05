<?php
/**
 * Donors
 *
 * @package     Give
 * @subpackage  Admin/Donors
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Donors Page
 *
 * Renders the donors page contents.
 *
 * @since  2.3
 * @return void
*/
function give_donors_page() {
	$default_views = give_donor_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'donors';
	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[$requested_view] ) ) {
		give_render_donor_view( $requested_view, $default_views );
	} else {
		give_donors_list();
	}
}

/**
 * Register the views for donor management
 *
 * @since  2.3
 * @return array Array of views and their callbacks
 */
function give_donor_views() {

	$views = array();
	return apply_filters( 'give_donor_views', $views );

}

/**
 * Register the tabs for donor management
 *
 * @since  2.3
 * @return array Array of tabs for the donor
 */
function give_donor_tabs() {

	$tabs = array();
	return apply_filters( 'give_donor_tabs', $tabs );

}

/**
 * List table of donors
 *
 * @since  2.3
 * @return void
 */
function give_donors_list() {
	include( dirname( __FILE__ ) . '/class-donor-table.php' );

	$donors_table = new Give_Donor_Reports_Table();
	$donors_table->prepare_items();
	?>
	<div class="wrap">
		<h2><?php _e( 'Donors', 'give' ); ?></h2>
		<?php do_action( 'give_donors_table_top' ); ?>
		<form id="give-donors-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors' ); ?>">
			<?php
			$donors_table->search_box( __( 'Search Donors', 'give' ), 'give-donors' );
			$donors_table->display();
			?>
			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="give-donors" />
			<input type="hidden" name="view" value="donors" />
		</form>
		<?php do_action( 'give_donors_table_bottom' ); ?>
	</div>
	<?php
}

/**
 * Renders the donor view wrapper
 *
 * @since  2.3
 * @param  string $view      The View being requested
 * @param  array $callbacks  The Registered views and their callback functions
 * @return void
 */
function give_render_donor_view( $view, $callbacks ) {

	$render = true;

	$donor_view_role = apply_filters( 'give_view_donors_role', 'view_shop_reports' );

	if ( ! current_user_can( $donor_view_role ) ) {
		give_set_error( 'give-no-access', __( 'You are not permitted to view this data.', 'give' ) );
		$render = false;
	}

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		give_set_error( 'give-invalid_donor', __( 'Invalid Donor ID Provided.', 'give' ) );
		$render = false;
	}

	$donor_id = (int)$_GET['id'];
	$donor    = new Give_Donor( $donor_id );

	if ( empty( $donor->id ) ) {
		give_set_error( 'give-invalid_donor', __( 'Invalid Donor ID Provided.', 'give' ) );
		$render = false;
	}

	$donor_tabs = give_donor_tabs();
	?>

	<div class='wrap'>
		<h2><?php _e( 'Donor Details', 'give' );?></h2>
		<?php if ( give_get_errors() ) :?>
			<div class="error settings-error">
				<?php give_print_errors(); ?>
			</div>
		<?php endif; ?>

		<?php if ( $donor && $render ) : ?>

			<div id="donor-tab-wrapper">
				<ul id="donor-tab-wrapper-list">
				<?php foreach ( $donor_tabs as $key => $tab ) : ?>
					<?php $active = $key === $view ? true : false; ?>
					<?php $class  = $active ? 'active' : 'inactive'; ?>

					<?php if ( ! $active ) : ?>
					<a title="<?php echo esc_attr( $tab['title'] ); ?>" aria-label="<?php echo esc_attr( $tab['title'] ); ?>" href="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=' . $key . '&id=' . $donor->id ) ); ?>">
					<?php endif; ?>

					<li class="<?php echo sanitize_html_class( $class ); ?>"><span class="dashicons <?php echo sanitize_html_class( $tab['dashicon'] ); ?>"></span></li>

					<?php if ( ! $active ) : ?>
					</a>
					<?php endif; ?>

				<?php endforeach; ?>
				</ul>
			</div>

			<div id="give-donor-card-wrapper" style="float: left">
				<?php $callbacks[$view]( $donor ) ?>
			</div>

		<?php endif; ?>

	</div>
	<?php

}


/**
 * View a donor
 *
 * @since  2.3
 * @param  $donor The Donor object being displayed
 * @return void
 */
function give_donors_view( $donor ) {

	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_shop_payments' );

	?>

	<?php do_action( 'give_donor_card_top', $donor ); ?>

	<div class="info-wrapper donor-section">

		<form id="edit-donor-info" method="post" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ); ?>">

			<div class="donor-info">

				<div class="avatar-wrap left" id="donor-avatar">
					<?php echo get_avatar( $donor->email ); ?><br />
					<?php if ( current_user_can( $donor_edit_role ) ): ?>
						<span class="info-item editable donor-edit-link"><a title="<?php _e( 'Edit Donor', 'give' ); ?>" href="#" id="edit-donor"><?php _e( 'Edit Donor', 'give' ); ?></a></span>
					<?php endif; ?>
				</div>

				<div class="donor-id right">
					#<?php echo $donor->id; ?>
				</div>

				<div class="donor-address-wrapper right">
				<?php if ( isset( $donor->user_id ) && $donor->user_id > 0 ) : ?>

					<?php
						$address = get_user_meta( $donor->user_id, '_give_user_address', true );
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

					<?php if ( ! empty( $address ) ) : ?>
					<strong><?php _e( 'Donor Address', 'give' ); ?></strong>
					<span class="donor-address info-item editable">
						<span class="info-item" data-key="line1"><?php echo $address['line1']; ?></span>
						<span class="info-item" data-key="line2"><?php echo $address['line2']; ?></span>
						<span class="info-item" data-key="city"><?php echo $address['city']; ?></span>
						<span class="info-item" data-key="state"><?php echo $address['state']; ?></span>
						<span class="info-item" data-key="country"><?php echo $address['country']; ?></span>
						<span class="info-item" data-key="zip"><?php echo $address['zip']; ?></span>
					</span>
					<?php endif; ?>
					<span class="donor-address info-item edit-item">
						<input class="info-item" type="text" data-key="line1" name="donorinfo[line1]" placeholder="<?php _e( 'Address 1', 'give' ); ?>" value="<?php echo $address['line1']; ?>" />
						<input class="info-item" type="text" data-key="line2" name="donorinfo[line2]" placeholder="<?php _e( 'Address 2', 'give' ); ?>" value="<?php echo $address['line2']; ?>" />
						<input class="info-item" type="text" data-key="city" name="donorinfo[city]" placeholder="<?php _e( 'City', 'give' ); ?>" value="<?php echo $address['city']; ?>" />
						<select data-key="country" name="donorinfo[country]" id="billing_country" class="billing_country give-select edit-item">
							<?php

							$selected_country = $address['country'];

							$countries = give_get_country_list();
							foreach( $countries as $country_code => $country ) {
								echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
							}
							?>
						</select>
						<?php
						$selected_state = give_get_shop_state();
						$states         = give_get_shop_states( $selected_country );

						$selected_state = isset( $address['state'] ) ? $address['state'] : $selected_state;

						if( ! empty( $states ) ) : ?>
						<select data-key="state" name="donorinfo[state]" id="card_state" class="card_state give-select info-item">
							<?php
								foreach( $states as $state_code => $state ) {
									echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
								}
							?>
						</select>
						<?php else : ?>
						<input type="text" size="6" data-key="state" name="donorinfo[state]" id="card_state" class="card_state give-input info-item" placeholder="<?php _e( 'State / Province', 'give' ); ?>"/>
						<?php endif; ?>
						<input class="info-item" type="text" data-key="zip" name="donorinfo[zip]" placeholder="<?php _e( 'Postal', 'give' ); ?>" value="<?php echo $address['zip']; ?>" />
					</span>
				<?php endif; ?>
				</div>

				<div class="donor-main-wrapper left">

					<span class="donor-name info-item edit-item"><input size="15" data-key="name" name="donorinfo[name]" type="text" value="<?php echo esc_attr( $donor->name ); ?>" placeholder="<?php _e( 'Donor Name', 'give' ); ?>" /></span>
					<span class="donor-name info-item editable"><span data-key="name"><?php echo $donor->name; ?></span></span>
					<span class="donor-name info-item edit-item"><input size="20" data-key="email" name="donorinfo[email]" type="text" value="<?php echo $donor->email; ?>" placeholder="<?php _e( 'Donor Email', 'give' ); ?>" /></span>
					<span class="donor-email info-item editable" data-key="email"><?php echo $donor->email; ?></span>
					<span class="donor-since info-item">
						<?php _e( 'Donor since', 'give' ); ?>
						<?php echo date_i18n( get_option( 'date_format' ), strtotime( $donor->date_created ) ) ?>
					</span>
					<span class="donor-user-id info-item edit-item">
						<?php

						$user_id    = $donor->user_id > 0 ? $donor->user_id : '';
						$data_atts  = array( 'key' => 'user_login', 'exclude' => $user_id );
						$user_args  = array(
							'name'  => 'donorinfo[user_login]',
							'class' => 'give-user-dropdown',
							'data'  => $data_atts,
						);

						if( ! empty( $user_id ) ) {
							$userdata = get_userdata( $user_id );
							$user_args['value'] = $userdata->user_login;
						}

						echo Give()->html->ajax_user_search( $user_args );
						?>
						<input type="hidden" name="donorinfo[user_id]" data-key="user_id" value="<?php echo $donor->user_id; ?>" />
					</span>

					<span class="donor-user-id info-item editable">
						<?php _e( 'User ID', 'give' ); ?>:&nbsp;
						<?php if( intval( $donor->user_id ) > 0 ) : ?>
							<span data-key="user_id"><?php echo $donor->user_id; ?></span>
						<?php else : ?>
							<span data-key="user_id"><?php _e( 'none', 'give' ); ?></span>
						<?php endif; ?>
						<?php if ( current_user_can( $donor_edit_role ) && intval( $donor->user_id ) > 0 ) : ?>
							<span class="disconnect-user"> - <a id="disconnect-donor" href="#disconnect" title="<?php _e( 'Disconnects the current user ID from this donor record', 'give' ); ?>"><?php _e( 'Disconnect User', 'give' ); ?></a></span>
						<?php endif; ?>
					</span>

				</div>

			</div>

			<span id="donor-edit-actions" class="edit-item">
				<input type="hidden" data-key="id" name="donorinfo[id]" value="<?php echo $donor->id; ?>" />
				<?php wp_nonce_field( 'edit-donor', '_wpnonce', false, true ); ?>
				<input type="hidden" name="give_action" value="edit-donor" />
				<input type="submit" id="give-edit-donor-save" class="button-secondary" value="<?php _e( 'Update Donor', 'give' ); ?>" />
				<a id="give-edit-donor-cancel" href="" class="delete"><?php _e( 'Cancel', 'give' ); ?></a>
			</span>

		</form>
	</div>

	<?php do_action( 'give_donor_before_stats', $donor ); ?>

	<div id="donor-stats-wrapper" class="donor-section">
		<ul>
			<li>
				<a title="<?php _e( 'View All Purchases', 'give' ); ?>" href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&user=' . urlencode( $donor->email ) ); ?>">
					<span class="dashicons dashicons-cart"></span>
					<?php printf( _n( '%d Completed Sale', '%d Completed Sales', $donor->purchase_count, 'give' ), $donor->purchase_count ); ?>
				</a>
			</li>
			<li>
				<span class="dashicons dashicons-chart-area"></span>
				<?php echo give_currency_filter( give_format_amount( $donor->purchase_value ) ); ?> <?php _e( 'Lifetime Value', 'give' ); ?>
			</li>
			<?php do_action( 'give_donor_stats_list', $donor ); ?>
		</ul>
	</div>

	<?php do_action( 'give_donor_before_tables_wrapper', $donor ); ?>

	<div id="donor-tables-wrapper" class="donor-section">

		<?php do_action( 'give_donor_before_tables', $donor ); ?>

		<h3><?php _e( 'Recent Payments', 'give' ); ?></h3>
		<?php
			$payment_ids = explode( ',', $donor->payment_ids );
			$payments    = give_get_payments( array( 'post__in' => $payment_ids ) );
			$payments    = array_slice( $payments, 0, 10 );
		?>
		<table class="wp-list-table widefat striped payments">
			<thead>
				<tr>
					<th><?php _e( 'ID', 'give' ); ?></th>
					<th><?php _e( 'Amount', 'give' ); ?></th>
					<th><?php _e( 'Date', 'give' ); ?></th>
					<th><?php _e( 'Status', 'give' ); ?></th>
					<th><?php _e( 'Actions', 'give' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $payments ) ) : ?>
					<?php foreach ( $payments as $payment ) : ?>
						<tr>
							<td><?php echo $payment->ID; ?></td>
							<td><?php echo give_payment_amount( $payment->ID ); ?></td>
							<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) ); ?></td>
							<td><?php echo give_get_payment_status( $payment, true ); ?></td>
							<td>
								<a title="<?php _e( 'View Details for Payment', 'give' ); echo ' ' . $payment->ID; ?>" href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&id=' . $payment->ID ); ?>">
									<?php _e( 'View Details', 'give' ); ?>
								</a>
								<?php do_action( 'give_donor_recent_purcahses_actions', $donor, $payment ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="5"><?php _e( 'No Payments Found', 'give' ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>

		<h3><?php printf( __( 'Purchased %s', 'give' ), give_get_label_plural() ); ?></h3>
		<?php
			$downloads = give_get_users_purchased_products( $donor->email );
		?>
		<table class="wp-list-table widefat striped downloads">
			<thead>
				<tr>
					<th><?php echo give_get_label_singular(); ?></th>
					<th width="120px"><?php _e( 'Actions', 'give' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $downloads ) ) : ?>
					<?php foreach ( $downloads as $download ) : ?>
						<tr>
							<td><?php echo $download->post_title; ?></td>
							<td>
								<a title="<?php echo esc_attr( sprintf( __( 'View %s', 'give' ), $download->post_title ) ); ?>" href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $download->ID ) ); ?>">
									<?php printf( __( 'View %s', 'give' ), give_get_label_singular() ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="2"><?php printf( __( 'No %s Found', 'give' ), give_get_label_plural() ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php do_action( 'give_donor_after_tables', $donor ); ?>

	</div>

	<?php do_action( 'give_donor_card_bottom', $donor ); ?>

	<?php
}

/**
 * View the notes of a donor
 *
 * @since  1.0
 * @param  $donor The Donor being displayed
 * @return void
 */
function give_donor_notes_view( $donor ) {

	$paged       = isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$paged       = absint( $paged );
	$note_count  = $donor->get_notes_count();
	$per_page    = apply_filters( 'give_donor_notes_per_page', 20 );
	$total_pages = ceil( $note_count / $per_page );

	$donor_notes = $donor->get_notes( $per_page, $paged );
	?>

	<div id="donor-notes-wrapper">
		<div class="donor-notes-header">
			<?php echo get_avatar( $donor->email, 30 ); ?> <span><?php echo $donor->name; ?></span>
		</div>
		<h3><?php _e( 'Notes', 'give' ); ?></h3>

		<?php if ( 1 == $paged ) : ?>
		<div style="display: block; margin-bottom: 35px;">
			<form id="give-add-donor-note" method="post" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=notes&id=' . $donor->id ); ?>">
				<textarea id="donor-note" name="donor_note" class="donor-note-input" rows="10"></textarea>
				<br />
				<input type="hidden" id="donor-id" name="donor_id" value="<?php echo $donor->id; ?>" />
				<input type="hidden" name="give_action" value="add-donor-note" />
				<?php wp_nonce_field( 'add-donor-note', 'add_donor_note_nonce', true, true ); ?>
				<input id="add-donor-note" class="right button-primary" type="submit" value="Add Note" />
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

		<div id="give-donor-notes">
		<?php if ( count( $donor_notes ) > 0 ) : ?>
			<?php foreach( $donor_notes as $key => $note ) : ?>
				<div class="donor-note-wrapper dashboard-comment-wrap comment-item">
					<span class="note-content-wrap">
						<?php echo stripslashes( $note ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<div class="give-no-donor-notes">
				<?php _e( 'No Donor Notes', 'give' ); ?>
			</div>
		<?php endif; ?>
		</div>

		<?php echo paginate_links( $pagination_args ); ?>

	</div>

	<?php
}

function give_donors_delete_view( $donor ) {
	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_shop_payments' );

	?>

	<?php do_action( 'give_donor_delete_top', $donor ); ?>

	<div class="info-wrapper donor-section">

		<form id="delete-donor" method="post" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=delete&id=' . $donor->id ); ?>">

			<div class="donor-notes-header">
				<?php echo get_avatar( $donor->email, 30 ); ?> <span><?php echo $donor->name; ?></span>
			</div>


			<div class="donor-info delete-donor">

				<span class="delete-donor-options">
					<p>
						<?php echo Give()->html->checkbox( array( 'name' => 'give-donor-delete-confirm' ) ); ?>
						<label for="give-donor-delete-confirm"><?php _e( 'Are you sure you want to delete this donor?', 'give' ); ?></label>
					</p>

					<p>
						<?php echo Give()->html->checkbox( array( 'name' => 'give-donor-delete-records', 'options' => array( 'disabled' => true ) ) ); ?>
						<label for="give-donor-delete-records"><?php _e( 'Delete all associated payments and records?', 'give' ); ?></label>
					</p>

					<?php do_action( 'give_donor_delete_inputs', $donor ); ?>
				</span>

				<span id="donor-edit-actions">
					<input type="hidden" name="donor_id" value="<?php echo $donor->id; ?>" />
					<?php wp_nonce_field( 'delete-donor', '_wpnonce', false, true ); ?>
					<input type="hidden" name="give_action" value="delete-donor" />
					<input type="submit" disabled="disabled" id="give-delete-donor" class="button-primary" value="<?php _e( 'Delete Donor', 'give' ); ?>" />
					<a id="give-delete-donor-cancel" href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ); ?>" class="delete"><?php _e( 'Cancel', 'give' ); ?></a>
				</span>

			</div>

		</form>
	</div>

	<?php

	do_action( 'give_donor_delete_bottom', $donor );
}
