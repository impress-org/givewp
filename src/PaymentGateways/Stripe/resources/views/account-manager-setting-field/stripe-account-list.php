<div
	id="give-stripe-connected"
	class="stripe-btn-disabled give-hidden"
	data-status="connected"
	data-title="<?php echo $modal_title; ?>"
	data-first-detail="<?php echo $modal_first_detail; ?>"
	data-second-detail="<?php echo $modal_second_detail; ?>"
	data-display="<?php echo $can_display; ?>"
	data-redirect-url="<?php echo esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ); ?>"
>
</div>
<?php if ( ! $stripe_accounts ) : ?>
	<div class="give-stripe-account-manager-list-item">
		<span><?php esc_html_e( 'No Stripe Accounts Connected.', 'give' ); ?></span>
	</div>
	<?php return; ?>
<?php endif; ?>

<div class="give-stripe-account-manager-list">
	<?php foreach ( $stripe_accounts as $slug => $details ) : ?>
		<?php
		$account_name       = $details['account_name'];
		$account_email      = $details['account_email'];
		$stripe_account_id  = $details['account_id'];
		$disconnect_message = esc_html__( 'Are you sure you want to disconnect this Stripe account?', 'give' );
		$disconnect_url     = add_query_arg(
			[
				'post_type'                   => 'give_forms',
				'page'                        => 'give-settings',
				'tab'                         => 'gateways',
				'section'                     => 'stripe-settings',
				'give_action'                 => ( 'connect' === $details['type'] )
					? 'disconnect_connected_stripe_account'
					: 'disconnect_manual_stripe_account',
				'give_stripe_disconnect_slug' => $slug,
			],
			wp_nonce_url( admin_url( 'edit.php' ), 'give_disconnect_connected_stripe_account_' . $slug )
		);

		$class = $slug === $default_account ? ' give-stripe-account-manager-list-item--default-account' : '';
		?>
		<div id="give-stripe-<?php echo $slug; ?>" class="give-stripe-account-manager-list-item<?php echo $class; ?>">
			<?php if ( $slug === $default_account ) : ?>
				<div class="give-stripe-account-default-checkmark">
					<svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M32.375 16.1875C32.375 25.1276 25.1276 32.375 16.1875 32.375C7.24737 32.375 0 25.1276 0 16.1875C0 7.24737 7.24737 0 16.1875 0C25.1276 0 32.375 7.24737 32.375 16.1875ZM14.3151 24.7586L26.3252 12.7486C26.733 12.3407 26.733 11.6795 26.3252 11.2717L24.8483 9.79474C24.4404 9.38686 23.7792 9.38686 23.3713 9.79474L13.5766 19.5894L9.00371 15.0165C8.59589 14.6086 7.93462 14.6086 7.52673 15.0165L6.04982 16.4934C5.642 16.9012 5.642 17.5625 6.04982 17.9703L12.8381 24.7586C13.246 25.1665 13.9072 25.1665 14.3151 24.7586Z" fill="#69B868"/>
					</svg>
				</div>
			<?php endif; ?>

			<div class="give-stripe-account-name-wrap">
				<div class="give-stripe-account-name">
					<span class="give-stripe-label"><?php _e( 'Account name:', 'give' ); ?></span>
					<?php echo esc_html( $account_name ); ?>
				</div>
				<div class="give-stripe-account-email">
					<span class="give-stripe-label"><?php _e( 'Account email:', 'give' ); ?></span>
					<?php echo esc_html( $account_email ); ?>
				</div>
				<div class="give-stripe-connection-method">
					<span class="give-stripe-label"><?php esc_html_e( 'Connection Method:', 'give' ); ?></span>
					<?php echo give_stripe_connection_type_name( $details['type'] ); ?>
				</div>
				<span class="give-stripe-account-edit">
					<?php if ( 'connect' !== $details['type'] ) : ?>
						<a class="give-stripe-account-edit-name" href="#"><?php esc_html_e( 'Edit', 'give' ); ?></a>
						<a
							class="give-stripe-account-update-name give-hidden"
							href="#"
							data-account="<?php echo $slug; ?>"
						><?php esc_html_e( 'Update', 'give' ); ?></a>
						<a class="give-stripe-account-cancel-name give-hidden" href="#"><?php esc_html_e( 'Cancel', 'give' ); ?></a>
					<?php endif; ?>
				</span>
			</div>

			<div class="give-stripe-account-actions">
				<span class="give-stripe-label"><?php esc_html_e( 'Connection Status:', 'give' ); ?></span>
				<?php
				if ( $slug !== $default_account || count( $stripe_accounts ) === 1 ) :
					?>
					<div class="give-stripe-account-connected">
						<?php esc_html_e( 'Connected', 'give' ); ?>
					</div>
					<div class="give-stripe-account-disconnect">
						<a
							class="give-stripe-disconnect-account-btn"
							href="<?php echo $disconnect_url; ?>"
							data-disconnect-message="<?php echo $disconnect_message; ?>"
							data-account="<?php echo $slug; ?>"
						><?php esc_html_e( 'Remove', 'give' ); ?></a>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( $slug === $default_account ) : ?>
				<div class="give-stripe-account-default give-stripe-account-badge">
					<?php esc_html_e( 'Default Account', 'give' ); ?>
				</div>
			<?php else : ?>
				<div class="give-stripe-account-default">
					<a
						data-account="<?php echo $slug; ?>"
						data-url="<?php echo give_stripe_get_admin_settings_page_url(); ?>"
						class="give-stripe-account-set-default" href="#"
					><?php esc_html_e( 'Set as Default', 'give' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
