<?php
/**
 * This template is used to display the donation history of the current user.
 */

$donations             = array();
$donation_history_args = Give()->session->get( 'give_donation_history_args' );

// User's Donations.
if ( is_user_logged_in() ) {
	$donations = give_get_users_donations( get_current_user_id(), 20, true, 'any' );
} elseif ( Give()->email_access->token_exists ) {
	// Email Access Token?
	$donations = give_get_users_donations( 0, 20, true, 'any' );
} elseif (
	false !== Give()->session->get_session_expiration() ||
	true === give_get_history_session()
) {
	// Session active?
	$email           = Give()->session->get( 'give_email' );
	$donor           = Give()->donors->get_donor_by( 'email', $email );
	$donations_count = count( explode( ',', $donor->payment_ids ) );

	if ( $donations_count > give_get_limit_display_donations() ) {

		// Restrict Security Email Access option, if donation count of a donor is less than or equal to limit.
		if ( true !== Give_Cache::get( "give_cache_email_throttle_limit_exhausted_{$donor->id}" ) ) {
			add_action( 'give_donation_history_table_end', 'give_donation_history_table_end' );
		} else {
			$value = Give()->email_access->verify_throttle / 60;

			/**
			 * Filter to modify email access exceed notices message.
			 *
			 * @since 2.1.3
			 *
			 * @param string $message email access exceed notices message
			 * @param int $value email access exceed times
			 *
			 * @return string $message email access exceed notices message
			 */
			$message = (string) apply_filters(
				'give_email_access_requests_exceed_notice',
				sprintf(
					__( 'Too many access email requests detected. Please wait %s before requesting a new donation history access link.', 'give' ),
					sprintf( _n( '%s minute', '%s minutes', $value, 'give' ), $value )
				),
				$value
			);

			give_set_error(
				'give-limited-throttle',
				$message
			);
		}

		$donations = give_get_users_donations( $email, give_get_limit_display_donations(), true, 'any' );
	} else {
		$donations = give_get_users_donations( $email, 20, true, 'any' );
	}
}

Give()->notices->render_frontend_notices( 0 );

if ( $donations ) : ?>
	<?php
	$table_headings = array(
		'id'             => __( 'ID', 'give' ),
		'date'           => __( 'Date', 'give' ),
		'donor'          => __( 'Donor', 'give' ),
		'amount'         => __( 'Amount', 'give' ),
		'status'         => __( 'Status', 'give' ),
		'payment_method' => __( 'Payment Method', 'give' ),
		'details'        => __( 'Details', 'give' ),
	);
	?>
	<div class="give_user_history_main" >
		<div class="give_user_history_notice"></div>
		<table id="give_user_history" class="give-table">
			<thead>
			<tr class="give-donation-row">
				<?php
				/**
				 * Fires in current user donation history table, before the header row start.
				 *
				 * Allows you to add new <th> elements to the header, before other headers in the row.
				 *
				 * @since 1.7
				 */
				do_action( 'give_donation_history_header_before' );

				foreach ( $donation_history_args as $index => $value ) {
					if ( filter_var( $donation_history_args[ $index ], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<th scope="col" class="give-donation-%1$s>">%2$s</th>',
							$index,
							$table_headings[ $index ]
						);
					endif;
				}

				/**
				 * Fires in current user donation history table, after the header row ends.
				 *
				 * Allows you to add new <th> elements to the header, after other headers in the row.
				 *
				 * @since 1.7
				 */
				do_action( 'give_donation_history_header_after' );
				?>
			</tr>
			</thead>
			<?php
			foreach ( $donations as $post ) :
				setup_postdata( $post );
				$donation_data = give_get_payment_meta( $post->ID );
				?>
				<tr class="give-donation-row">
					<?php
					/**
					 * Fires in current user donation history table, before the row starts.
					 *
					 * Allows you to add new <td> elements to the row, before other elements in the row.
					 *
					 * @since 1.7
					 *
					 * @param int   $post_id       The ID of the post.
					 * @param mixed $donation_data Payment meta data.
					 */
					do_action( 'give_donation_history_row_start', $post->ID, $donation_data );

					if ( filter_var( $donation_history_args['id'], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<td class="give-donation-id"><span class="give-mobile-title">%2$s</span>%1$s</td>',
							give_get_payment_number( $post->ID ),
							esc_html( $table_headings['id'] )
						);
					endif;

					if ( filter_var( $donation_history_args['date'], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<td class="give-donation-date"><span class="give-mobile-title">%2$s</span>%1$s</td>',
							date_i18n( give_date_format(), strtotime( get_post_field( 'post_date', $post->ID ) ) ),
							esc_html( $table_headings['date'] )
						);
					endif;

					if ( filter_var( $donation_history_args['donor'], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<td class="give-donation-donor"><span class="give-mobile-title">%2$s</span>%1$s</td>',
							give_get_donor_name_by( $post->ID ),
							$table_headings['donor']
						);
					endif;
					?>

					<?php if ( filter_var( $donation_history_args['amount'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
						<td class="give-donation-amount">
						<?php printf( '<span class="give-mobile-title">%1$s</span>', esc_html( $table_headings['amount'] ) ); ?>
						<span class="give-donation-amount">
							<?php
							$currency_code   = give_get_payment_currency_code( $post->ID );
							$donation_amount = give_donation_amount( $post->ID, true );

							/**
							 * Filters the donation amount on Donation History Page.
							 *
							 * @param int $donation_amount Donation Amount.
							 * @param int $post_id         Donation ID.
							 *
							 * @since 1.8.13
							 *
							 * @return int
							 */
							echo apply_filters( 'give_donation_history_row_amount', $donation_amount, $post->ID );
							?>
						</span>
						</td>
					<?php endif; ?>

					<?php
					if ( filter_var( $donation_history_args['status'], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<td class="give-donation-status"><span class="give-mobile-title">%2$s</span>%1$s</td>',
							give_get_payment_status( $post, true ),
							esc_html( $table_headings['status'] )
						);
					endif;

					if ( filter_var( $donation_history_args['payment_method'], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<td class="give-donation-payment-method"><span class="give-mobile-title">%2$s</span>%1$s</td>',
							give_get_gateway_checkout_label( give_get_payment_gateway( $post->ID ) ),
							esc_html( $table_headings['payment_method'] )
						);
					endif;
					?>
					<td class="give-donation-details">
						<?php
						// Display View Receipt or.
						if ( 'publish' !== $post->post_status && 'subscription' !== $post->post_status  ) :
							echo sprintf(
								'<span class="give-mobile-title">%4$s</span><a href="%1$s"><span class="give-donation-status %2$s">%3$s</span></a>',
								esc_url(
									add_query_arg(
										'donation_id',
										$post->ID,
                                        $_SERVER['REQUEST_URI']
									)
								),
								$post->post_status,
								__( 'View', 'give' ) . ' ' . give_get_payment_status( $post, true ) . ' &raquo;',
								esc_html( $table_headings['details'] )
							);

						else :
							echo sprintf(
								'<span class="give-mobile-title">%3$s</span><a href="%1$s">%2$s</a>',
								esc_url(
									add_query_arg(
										'donation_id',
										$post->ID,
                                        $_SERVER['REQUEST_URI']
                                    )
								),
								__( 'View Receipt &raquo;', 'give' ),
								esc_html( $table_headings['details'] )
							);

						endif;
						?>
					</td>
					<?php
					/**
					 * Fires in current user donation history table, after the row ends.
					 *
					 * Allows you to add new <td> elements to the row, after other elements in the row.
					 *
					 * @since 1.7
					 *
					 * @param int   $post_id       The ID of the post.
					 * @param mixed $donation_data Payment meta data.
					 */
					do_action( 'give_donation_history_row_end', $post->ID, $donation_data );
					?>
				</tr>
			<?php endforeach; ?>

			<?php
			/**
			 * Fires in footer of user donation history table.
			 *
			 * Allows you to add new <tfoot> elements to the row, after other elements in the row.
			 *
			 * @since 1.8.17
			 */
			do_action( 'give_donation_history_table_end' );
			?>
		</table>
		<div id="give-donation-history-pagination" class="give_pagination navigation">
			<?php
			$big = 999999;
			echo paginate_links(
				array(
					'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format'  => '?paged=%#%',
					'current' => max( 1, get_query_var( 'paged' ) ),
					'total'   => ceil( give_count_donations_of_donor() / 20 ), // 20 items per page
				)
			);
			?>
		</div>
	</div>
	<?php wp_reset_postdata(); ?>
<?php else : ?>
	<?php Give_Notices::print_frontend_notice( __( 'It looks like you haven\'t made any donations.', 'give' ), true, 'success' ); ?>
	<?php
endif;
