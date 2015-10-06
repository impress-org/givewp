<?php
/**
 * This template is used to display the donation history of the current user.
 */
$donations = give_get_users_purchases( get_current_user_id(), 20, true, 'any' );
if ( $donations ) : ?>
	<table id="give_user_history" class="give-table">
		<thead>
		<tr class="give_purchase_row">
			<?php do_action( 'give_purchase_history_header_before' ); ?>
			<th class="give_purchase_id"><?php _e( 'ID', 'give' ); ?></th>
			<th class="give_purchase_date"><?php _e( 'Date', 'give' ); ?></th>
			<th class="give_purchase_amount"><?php _e( 'Amount', 'give' ); ?></th>
			<th class="give_purchase_details"><?php _e( 'Details', 'give' ); ?></th>
			<?php do_action( 'give_purchase_history_header_after' ); ?>
		</tr>
		</thead>
		<?php foreach ( $donations as $post ) : setup_postdata( $post ); ?>
			<?php $donation_data = give_get_payment_meta( $post->ID ); ?>
			<tr class="give_purchase_row">
				<?php do_action( 'give_purchase_history_row_start', $post->ID, $donation_data ); ?>
				<td class="give_purchase_id">#<?php echo give_get_payment_number( $post->ID ); ?></td>
				<td class="give_purchase_date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( get_post_field( 'post_date', $post->ID ) ) ); ?></td>
				<td class="give_purchase_amount">
					<span class="give_purchase_amount"><?php echo give_currency_filter( give_format_amount( give_get_payment_amount( $post->ID ) ) ); ?></span>
				</td>
				<td class="give_purchase_details">
					<?php
					if ( $post->post_status != 'publish' && $post->post_status != 'subscription' ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'payment_key', give_get_payment_key( $post->ID ), give_get_success_page_uri() ) ); ?>"><span class="give_purchase_status <?php echo $post->post_status; ?>"><?php echo give_get_payment_status( $post, true ); ?></span> &raquo;</a>
					<?php else: ?>
						<a href="<?php echo esc_url( add_query_arg( 'payment_key', give_get_payment_key( $post->ID ), give_get_success_page_uri() ) ); ?>"><?php _e( 'View Details', 'give' ); ?>&raquo;</a>
					<?php endif; ?>
				</td>
				<?php do_action( 'give_purchase_history_row_end', $post->ID, $donation_data ); ?>
			</tr>
		<?php endforeach; ?>
	</table>
	<div id="give_purchase_history_pagination" class="give_pagination navigation">
		<?php
		$big = 999999;
		echo paginate_links( array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, get_query_var( 'paged' ) ),
			'total'   => ceil( give_count_purchases_of_customer() / 20 ) // 20 items per page
		) );
		?>
	</div>
	<?php wp_reset_postdata(); ?>
<?php else : ?>
	<p class="give-no-purchases"><?php _e( 'It looks like you haven\'t made any donations', 'give' ); ?>.</p>
<?php endif;
