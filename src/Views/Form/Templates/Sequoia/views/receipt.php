<?php

use Give\Views\IframeView;
use function Give\Helpers\Form\Template\get as getTemplateOptions;
use function Give\Helpers\Form\Template\Utils\Frontend\getPaymentId;
use function give_get_gateway_admin_label as getGatewayLabel;
use function give_get_payment_status as getDonationStatusLabel;
use function give_currency_filter as filterCurrency;
use function give_sanitize_amount as sanitizeAmount;
use function give_do_email_tags as formatContent;
use Give_Payment as Payment;

/* @var Payment $donation */
$donation = new Payment( getPaymentId() );
$options  = getTemplateOptions();

ob_start();
?>
<div class="give-receipt-wrap give-embed-receipt">
	<div class="give-section receipt">
		<?php if ( ! empty( $options['thank-you']['image'] ) ) : ?>
			<div class="image">
				<img src="<?php echo $options['thank-you']['image']; ?>"/>
			</div>
		<?php else : ?>
			<div class="checkmark">
				<i class="fas fa-check"></i>
			</div>
		<?php endif; ?>
		<h2 class="headline">
			<?php echo $options['thank-you']['headline']; ?>
		</h2>
		<p class="message">
			<?php echo formatContent( $options['thank-you']['description'], [ 'payment_id' => $donation->ID ] ); ?>
		</p>
		<?php if ( isset( $options['thank-you']['sharing'] ) && $options['thank-you']['sharing'] === 'enabled' ) : ?>
			<div class="social-sharing">
				<p class="instruction">
					<?php echo $options['thank-you']['sharing_instruction']; ?>
				</p>
				<div class="btn-row">
					<!-- Use inline onclick listener to avoid popup blockers -->
					<button class="give-btn social-btn facebook-btn"
						onclick="
							// Retrieve and sanitize url to be shared
							let url = parent.window.location.toString();
							if (window.Give.fn.getParameterByName('giveDonationAction', url)) {
								url = window.Give.fn.removeURLParameter(url, 'giveDonationAction');
								url = window.Give.fn.removeURLParameter(url, 'payment-confirmation');
								url = window.Give.fn.removeURLParameter(url, 'payment-id');
							}
							// Calculate new window position, based on parent window height/width
							const top = parent.window.innerHeight / 2 - 365;
							const left = parent.window.innerWidth / 2 - 280;
							// Open new window with prompt for Facebook sharing
							window.Give.share.fn.facebook(url);
							return false;
							">
						<?php _e( 'Share on Facebook', 'give' ); ?><i class="fab fa-facebook"></i>
					</button>
					<!-- Use inline onclick listener to avoid popup blockers -->
					<button
						class="give-btn social-btn twitter-btn"
						onclick="
							// Retrieve and sanitize url to be shared
							let url = parent.window.location.toString();
							if (window.Give.fn.getParameterByName('giveDonationAction', url)) {
								url = window.Give.fn.removeURLParameter(url, 'giveDonationAction');
								url = window.Give.fn.removeURLParameter(url, 'payment-confirmation');
								url = window.Give.fn.removeURLParameter(url, 'payment-id');
							}
							const text = `<?php echo urlencode( $options['thank-you']['twitter_message'] ); ?>`;
							// Open new window with prompt for Twitter sharing
							window.Give.share.fn.twitter(url, text);
							return false;
						">
						<?php _e( 'Share on Twitter', 'give' ); ?><i class="fab fa-twitter"></i>
					</button>
				</div>
			</div>
		<?php endif; ?>
		<div class="details">
			<h3 class="headline"><?php _e( 'Donation Details', 'give' ); ?></h3>

			<!-- Donor Details -->
			<div class="details-table">
				<div class="details-row">
					<i class="fas fa-user"></i>
					<div class="detail">
						<?php _e( 'Donor Name', 'give' ); ?>
					</div>
					<div class="value">
						<?php echo "{$donation->first_name} {$donation->last_name}"; ?>
					</div>
				</div>
				<div class="details-row">
					<i class="fas fa-envelope"></i>
					<div class="detail">
						<?php _e( 'Email Address', 'give' ); ?>
					</div>
					<div class="value">
						<?php echo $donation->email; ?>
					</div>
				</div>
				<?php if ( ! empty( $donation->address['line1'] ) ) : ?>
					<div class="details-row">
						<i class="fas fa-envelope"></i>
						<div class="detail">
							<?php _e( 'Billing Address', 'give' ); ?>
						</div>
						<div class="value">
							<?php
							printf(
								'%1$s<br>%2$s%3$s,%4$s%5$s<br>%6$s',
								$donation->address['line1'],
								! empty( $donation->address['line2'] ) ? $donation->address['line2'] . '<br>' : '',
								$donation->address['city'],
								$donation->address['state'],
								$donation->address['zip'],
								$donation->address['country']
							)
							?>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Payment Details -->
			<div class="details-table payment-details">
				<div class="details-row">
					<div class="detail">
						<?php _e( 'Payment Method', 'give' ); ?>
					</div>
					<div class="value">
						<?php echo getGatewayLabel( $donation->gateway ); ?>
					</div>
				</div>
				<div class="details-row">
					<div class="detail">
						<?php _e( 'Payment Status', 'give' ); ?>
					</div>
					<div class="value">
						<?php echo getDonationStatusLabel( $donation->ID, true ); ?>
					</div>
				</div>
				<div class="details-row">
					<div class="detail">
						<?php _e( 'Donation Amount', 'give' ); ?>
					</div>
					<div class="value">
						<?php
						echo filterCurrency(
							sanitizeAmount( $donation->subtotal ),
							[
								'currency_code'   => $donation->currency,
								'decode_currency' => true,
								'form_id'         => $donation->form_id,
							]
						);
						?>
					</div>
				</div>
				<div class="details-row">
					<div class="detail">
						<?php _e( 'Processing Fees', 'give' ); ?>
					</div>
					<div class="value">
						<?php
						$fees = $donation->total - $donation->subtotal;
						echo filterCurrency(
							sanitizeAmount( $fees ),
							[
								'currency_code'   => $donation->currency,
								'decode_currency' => true,
								'form_id'         => $donation->form_id,
							]
						);
						?>
					</div>
				</div>
				<div class="details-row total">
					<div class="detail">
						<?php _e( 'Donation Total', 'give' ); ?>
					</div>
					<div class="value">
						<?php
						echo filterCurrency(
							sanitizeAmount( $donation->total ),
							[
								'currency_code'   => $donation->currency,
								'decode_currency' => true,
								'form_id'         => $donation->form_id,
							]
						);
						?>
					</div>
				</div>
			</div>
		</div>

		<!-- Download Receipt TODO: make this conditional on presence of pdf receipts addon -->
		<button class="give-btn download-btn">
			<?php _e( 'Donation Receipt', 'give' ); ?> <i class="fas fa-file-pdf"></i>
		</button>
	</div>
	<div class="form-footer">
		<div class="secure-notice">
			<i class="fas fa-lock"></i>
			<?php _e( 'Secure Donation', 'give' ); ?>
		</div>
	</div>
</div>


<?php
$iframeView = new IframeView();

echo $iframeView->setTitle( __( 'Donation Receipt', 'give' ) )
				->setBody( ob_get_clean() )
				->render();
?>
