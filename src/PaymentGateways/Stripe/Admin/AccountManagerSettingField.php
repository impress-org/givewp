<?php

namespace Give\PaymentGateways\Stripe\Admin;

use Give\PaymentGateways\Stripe\Repositories\AccountDetail;

/**
 * Class AccountManagerSettingField
 * @package Give\PaymentGateways\Stripe\Admin
 * @unreleased
 */
class AccountManagerSettingField {
	/**
	 * @var AccountDetail
	 */
	private $accountDetailRepository;

	/**
	 * @var array
	 */
	private $stripeAccounts;

	/**
	 * @var string
	 */
	private $defaultStripeAccountId;

	/**
	 * AccountManagerSettingField constructor.
	 *
	 * @unreleased
	 *
	 * @param  AccountDetail  $accountDetailRepository
	 */
	public function __construct( AccountDetail $accountDetailRepository ) {
		$this->accountDetailRepository = $accountDetailRepository;
	}

	/**
	 * @unreleased
	 */
	private function setUpProperties() {
		$this->stripeAccounts         = $this->accountDetailRepository->getAllStripeAccounts();
		$this->defaultStripeAccountId = $this->accountDetailRepository->getDefaultStripeAccountId();
	}

	/**
	 * Render Stripe account manager setting field.
	 *
	 * @unreleased
	 *
	 * @param  array  $field
	 */
	public function handle( $field ) {
		$this->setUpProperties();
		$classes = ! empty( $field['wrapper_class'] ) ? esc_attr( $field['wrapper_class'] ) : ''
		?>
		<tr valign="top" class="
		<?php
		echo $classes;
		?>
		">
			<td class="give-forminp give-forminp-api_key">
				<div id="give-stripe-account-manager-errors"></div>
				<?php
				$this->getIntroductionSectionMarkup();
				?>
				<div class="give-stripe-account-manager-container">
					<?php
					$this->getStripeAccountListSectionMarkup();
					$this->getAddNewStripeAccountSectionMarkup();
					?>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * @unreleased
	 */
	private function getIntroductionSectionMarkup() {
		?>
		<div id="give-stripe-account-manager-description">
			<h2>
				<?php
				esc_html_e( 'Manage Your Stripe Accounts', 'give' );
				?>
				</h2>
			<p class="give-field-description">
				<?php
				esc_html_e(
					'Connect to the Stripe payment gateway using this section. Multiple Stripe accounts can be connected simultaneously. All donation forms will use the "Default Account" unless configured otherwise. To specify a different Stripe account for a form, configure the settings within the "Stripe Account" tab on the individual form edit screen.',
					'give'
				);
				?>
			</p>
			<?php
			if ( ! give_stripe_is_premium_active() ) :
				?>
				<p class="give-field-description">
					<br/>
					<?php
					echo sprintf(
						__(
							'NOTE: You are using the free Stripe payment gateway integration. This includes an additional 2%% fee for processing one-time donations. This fee is removed by activating the premium <a href="%1$s" target="_blank">Stripe add-on</a> and never applies to subscription donations made through the <a href="%2$s" target="_blank">Recurring Donations add-on</a>. <a href="%3$s" target="_blank">Learn More ></a>',
							'give'
						),
						esc_url( 'http://docs.givewp.com/settings-stripe-addon' ),
						esc_url( 'http://docs.givewp.com/settings-stripe-recurring' ),
						esc_url( 'http://docs.givewp.com/settings-stripe-free' )
					);
					?>
				</p>
				<?php
			endif
			?>
		</div>
		<?php
	}

	/**
	 * @unreleased
	 */
	private function getStripeAccountListSectionMarkup() {
		$site_url            = get_site_url();
		$modal_title         = sprintf(
			'<strong>%1$s</strong>',
			esc_html__(
				'You are connected! Now this is important: Please configure your Stripe webhook to finalize the setup.',
				'give'
			)
		);
		$modal_first_detail  = sprintf(
			'%1$s %2$s',
			esc_html__(
				'In order for Stripe to function properly, you must add a new Stripe webhook endpoint. To do this please visit the <a href=\'https://dashboard.stripe.com/webhooks\' target=\'_blank\'>Webhooks Section of your Stripe Dashboard</a> and click the <strong>Add endpoint</strong> button and paste the following URL:',
				'give'
			),
			"<strong>{$site_url}?give-listener=stripe</strong>"
		);
		$modal_second_detail = esc_html__(
			'Stripe webhooks are required so GiveWP can communicate properly with the payment gateway to confirm payment completion, renewals, and more.',
			'give'
		);
		$can_display         = ! empty( $_GET['stripe_account'] ) ? '0' : '1';
		?>
		<div
			id="give-stripe-connected"
			class="stripe-btn-disabled give-hidden"
			data-status="connected"
			data-title="
			<?php
			echo $modal_title;
			?>
			"
			data-first-detail="
			<?php
			echo $modal_first_detail;
			?>
			"
			data-second-detail="
			<?php
			echo $modal_second_detail;
			?>
			"
			data-display="
			<?php
			echo $can_display;
			?>
			"
			data-redirect-url="
			<?php
			echo esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) );
			?>
			"
		>
		</div>
		<?php
		if ( ! $this->stripeAccounts ) :
			?>
			<div class="give-stripe-account-manager-list-item">
				<span>
				<?php
					esc_html_e( 'No Stripe Accounts Connected.', 'give' );
				?>
					</span>
			</div>
			<?php
			return;
		endif;
		?>

		<div class="give-stripe-account-manager-list">
			<?php
			foreach ( $this->stripeAccounts as $slug => $details ) :
				?>
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

				$class = $slug === $this->defaultStripeAccountId ? ' give-stripe-account-manager-list-item--default-account' :
					'';
				?>
				<div id="give-stripe-
				<?php
				echo $slug;
				?>
				" class="give-stripe-account-manager-list-item
				<?php
				echo $class;
				?>
				">
					<?php
					if ( $slug === $this->defaultStripeAccountId ) :
						?>
						<div class="give-stripe-account-default-checkmark">
							<svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M32.375 16.1875C32.375 25.1276 25.1276 32.375 16.1875 32.375C7.24737 32.375 0 25.1276 0 16.1875C0 7.24737 7.24737 0 16.1875 0C25.1276 0 32.375 7.24737 32.375 16.1875ZM14.3151 24.7586L26.3252 12.7486C26.733 12.3407 26.733 11.6795 26.3252 11.2717L24.8483 9.79474C24.4404 9.38686 23.7792 9.38686 23.3713 9.79474L13.5766 19.5894L9.00371 15.0165C8.59589 14.6086 7.93462 14.6086 7.52673 15.0165L6.04982 16.4934C5.642 16.9012 5.642 17.5625 6.04982 17.9703L12.8381 24.7586C13.246 25.1665 13.9072 25.1665 14.3151 24.7586Z" fill="#69B868"/>
							</svg>
						</div>
						<?php
					endif;
					?>

					<div class="give-stripe-account-name-wrap">
						<div class="give-stripe-account-name">
							<span class="give-stripe-label">
							<?php
								_e( 'Account name:', 'give' );
							?>
								</span>
							<?php
							echo esc_html( $account_name );
							?>
						</div>
						<div class="give-stripe-account-email">
							<span class="give-stripe-label">
							<?php
								_e( 'Account email:', 'give' );
							?>
								</span>
							<?php
							echo esc_html( $account_email );
							?>
						</div>
						<div class="give-stripe-connection-method">
							<span class="give-stripe-label">
							<?php
								esc_html_e( 'Connection Method:', 'give' );
							?>
								</span>
							<?php
							echo give_stripe_connection_type_name( $details['type'] );
							?>
						</div>
						<span class="give-stripe-account-edit">
					<?php
					if ( 'connect' !== $details['type'] ) :
						?>
						<a class="give-stripe-account-edit-name" href="#">
						<?php
							esc_html_e( 'Edit', 'give' );
						?>
							</a>
						<a
							class="give-stripe-account-update-name give-hidden"
							href="#"
							data-account="
							<?php
							echo $slug;
							?>
							"
						>
						<?php
							esc_html_e( 'Update', 'give' );
						?>
							</a>
						<a class="give-stripe-account-cancel-name give-hidden" href="#">
						<?php
							esc_html_e( 'Cancel', 'give' );
						?>
							</a>
						<?php
					endif;
					?>
				</span>
					</div>

					<div class="give-stripe-account-actions">
						<span class="give-stripe-label">
						<?php
							esc_html_e( 'Connection Status:', 'give' );
						?>
							</span>
						<?php
						if ( $slug !== $this->defaultStripeAccountId || count( $this->stripeAccounts ) === 1 ) :
							?>
							<div class="give-stripe-account-connected">
								<?php
								esc_html_e( 'Connected', 'give' );
								?>
							</div>
							<div class="give-stripe-account-disconnect">
								<a
									class="give-stripe-disconnect-account-btn"
									href="
									<?php
									echo $disconnect_url;
									?>
									"
									data-disconnect-message="
									<?php
									echo $disconnect_message;
									?>
									"
									data-account="
									<?php
									echo $slug;
									?>
									"
								>
								<?php
									esc_html_e( 'Remove', 'give' );
								?>
									</a>
							</div>
							<?php
						endif;
						?>
					</div>
					<?php
					if ( $slug === $this->defaultStripeAccountId ) :
						?>
						<div class="give-stripe-account-default give-stripe-account-badge">
							<?php
							esc_html_e( 'Default Account', 'give' );
							?>
						</div>
						<?php
					else :
						?>
						<div class="give-stripe-account-default">
							<a
								data-account="
								<?php
								echo $slug;
								?>
								"
								data-url="
								<?php
								echo give_stripe_get_admin_settings_page_url();
								?>
								"
								class="give-stripe-account-set-default" href="#"
							>
							<?php
								esc_html_e( 'Set as Default', 'give' );
							?>
								</a>
						</div>
						<?php
					endif;
					?>
				</div>
				<?php
			endforeach;
			?>
		</div>
		<?php
	}

	/**
	 * @unreleased
	 */
	private function getAddNewStripeAccountSectionMarkup() {
		if ( ! give_has_upgrade_completed( 'v270_store_stripe_account_for_donation' ) ) :
			?>
			<div class="give-stripe-account-manager-add-section">
				<?php
				Give()->notices->print_admin_notices(
					[
						'description' => sprintf(
							'%1$s <a href="%2$s">%3$s</a> %4$s',
							esc_html__(
								'Give 2.7.0 introduces the ability to connect a single site to multiple Stripe accounts. To use this feature, you need to complete database updates. ',
								'give'
							),
							esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-updates' ) ),
							esc_html__( 'Click here', 'give' ),
							esc_html__( 'to complete your pending database updates.', 'give' )
						),
						'dismissible' => false,
					]
				);
				?>
			</div>
			<?php
			return;
		endif;
		?>

		<div class="give-stripe-account-manager-add-section">
			<h3>
				<?php
				esc_html_e( 'Add a New Stripe Account', 'give' );
				?>
			</h3>
			<div class="give-stripe-add-account-errors"></div>
			<table class="form-table give-setting-tab-body give-setting-tab-body-gateways">
				<tbody>
					<?php
					if ( give_stripe_is_premium_active() ) {
						/**
						 * This action hook will be used to load Manual API fields for premium addon.
						 *
						 * @since 2.7.0
						 *
						 * @param  array  $$his->>stripeAccounts  All Stripe accounts.
						 *
						 */
						do_action( 'give_stripe_premium_manual_api_fields', $this->stripeAccounts );
					}
					?>
					<tr class="give-stripe-account-type-connect">
						<td class="give-forminp">
							<?php
							echo give_stripe_connect_button();
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
