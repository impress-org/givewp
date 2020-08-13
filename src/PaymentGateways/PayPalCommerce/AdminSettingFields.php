<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\Views\Admin\UpsellNotice;
use Give_License;

/**
 * Class AdminSettingFields
 * @since 2.8.0
 * @package Give\PaymentGateways\PayPalCommerce
 *
 */
class AdminSettingFields {
	/**
	 * Bootstrap fields.
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		add_action( 'give_admin_field_paypal_commerce_account_manger', [ $this, 'payPalCommerceAccountManagerField' ] );
		add_action( 'give_admin_field_paypal_commerce_introduction', [ $this, 'introductionSection' ] );
	}

	/**
	 * Paypal Checkout account manager custom field
	 *
	 * @since 2.8.0
	 */
	public function payPalCommerceAccountManagerField() {
		$recurringAddonInfo     = Give_License::get_plugin_by_slug( 'give-recurring' );
		$isRecurringAddonActive = isset( $recurringAddonInfo['Status'] ) && 'active' === $recurringAddonInfo['Status'];
		?>
		<table class="form-table give-setting-tab-body give-setting-tab-body-gateways">
			<tbody>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="give_paypal_commerce_country"><?php esc_html_e( 'PayPal Connection', 'give' ); ?></label>
					</th>
					<td class="give-forminp give-forminp-select">
						<div id="give-paypal-commerce-account-manager-field-wrap">
							<div class="connect-button-wrap">
								<?php
								/** @var MerchantDetails $accountRepository */
								$accountRepository = give( MerchantDetails::class );
								?>
								<div
									class="button-wrap connection-setting <?php echo $accountRepository->accountIsConnected() ? 'give-hidden' : ''; ?>">
									<div>
										<button class="button button-primary button-large" id="js-give-paypal-on-boarding-handler">
											<i class="fab fa-paypal"></i>&nbsp;&nbsp;
											<?php
											esc_html_e(
												'Connect with PayPal',
												'give'
											);
											?>
										</button>
										<a class="give-hidden" target="_blank"
										   data-paypal-onboard-complete="givePayPalOnBoardedCallback" href="#"
										   data-paypal-button="true">
											<?php esc_html_e( 'Sign up for PayPal', 'give' ); ?>
										</a>
										<span class="tooltip">
							<span class="left-arrow"></span>
							<?php esc_html_e( 'Click to get started!', 'give' ); ?>
						</span>
									</div>
									<span class="give-field-description">
							<i class="fa fa-exclamation"></i>
							<?php esc_html_e( 'PayPal is currently NOT connected.', 'give' ); ?>
						</span>
								</div>
								<div
									class="button-wrap disconnection-setting <?php echo ! $accountRepository->accountIsConnected() ? 'give-hidden' : ''; ?>">
									<div>
										<button class="button button-large disabled" disabled="disabled">
											<i class="fab fa-paypal"></i>&nbsp;&nbsp;<?php esc_html_e( 'Connected', 'give' ); ?>
										</button>
									</div>
									<div>
						<span class="give-field-description">
							<i class="fa fa-check"></i>
							<?php
							printf(
								'%1$s <span class="paypal-account-email">%2$s</span>',
								esc_html__( 'Connected for payments as', 'give' ),
								give( MerchantDetail::class )->merchantId
							);
							?>
						</span>
										<span class="actions">
							<a href="#"
							   id="js-give-paypal-disconnect-paypal-account"><?php esc_html_e( 'Disconnect', 'give' ); ?></a>
						</span>
									</div>
									<div class="api-access-feature-list-wrap">
										<p><?php esc_html_e( 'APIs Connected:', 'give' ); ?></p>
										<ul>
											<li><?php esc_html_e( 'Payments', 'give' ); ?></li>
											<?php if ( $isRecurringAddonActive ) : ?>
												<li><?php esc_html_e( 'Subscriptions', 'give' ); ?></li>
											<?php endif; ?>
											<li><?php esc_html_e( 'Refunds', 'give' ); ?></li>
										</ul>
									</div>

									<?php $accountErrors = give( MerchantDetails::class )->getAccountErrors(); ?>
									<?php if ( ! empty( $accountErrors ) ) : ?>
										<div>
							<span>
								<p class="error-message"><?php esc_html_e( 'Warning, your account is not ready to accept donations. Please review the following list:', 'give' ); ?></p>
								<ul class="ul-disc">
										<?php
										foreach ( $accountErrors as $error ) {
											echo "<li>{$error}</li>";
										}
										?>
								</ul>
								<p><a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&paypalStatusCheck' ); ?>"><?php esc_html_e( 'Re-Check Account Status', 'give' ); ?></a></p>
							</span>
										</div>
									<?php endif; ?>
								</div>

							</div>
							<?php echo UpsellNotice::recurringAddon(); ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}


	/**
	 * PayPal Commerce introduction section.
	 *
	 * @since 2.8.0
	 */
	public function introductionSection() {
		?>
		<div id="give-paypal-commerce-introduction-wrap">
			<div class="hero-section">
				<div>
					<h2><?php esc_html_e( 'Accept Donations with PayPal Commerce', 'give' ); ?></h2>
					<p class="give-field-description"><?php esc_html_e( 'Allow your donors to give using Debit or Credit Cards directly on your website with no additional fees. Upgrade to PayPal Pro and provide your donors with even more payment options using PayPal Smart Buttons.', 'give' ); ?></p>
				</div>
				<div class="paypal-logo">
					PayPal Logo
				</div>
			</div>
			<div class="feature-list">
				<div><i class="fa fa-angle-right"></i><?php esc_html_e( 'Credit and Debit Card Donations', 'give' ); ?>
				</div>
				<div>
					<i class="fa fa-angle-right"></i><?php esc_html_e( 'Improve donation conversion rates', 'give' ); ?>
				</div>
				<div><i class="fa fa-angle-right"></i><?php esc_html_e( 'Easy no-API key connection', 'give' ); ?></div>
				<div>
					<i class="fa fa-angle-right"></i><?php esc_html_e( 'Accept payments from around the world', 'give' ); ?>
				</div>
				<div><i class="fa fa-angle-right"></i><?php esc_html_e( 'PayPal, Apple and Google Pay', 'give' ); ?>
				</div>
			</div>
		</div>
		<?php
	}
}
