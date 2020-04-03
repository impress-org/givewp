<?php

use function Give\Helpers\Form\Theme\Utils\Frontend\getPaymentId as getPaymentId;
use function give_get_gateway_admin_label as getGatewayLabel;
use function give_currency_filter as filterCurrency;
use function give_sanitize_amount as sanitizeAmount;

use Give_Payment as Payment;

?>

<!DOCTYPE html>
<html lang="en" style="margin-top: 0 !important;">
	<head>
		<meta charset="utf-8">
		<title><?php _e( 'Donation Receipt', 'give' ); ?></title>

		<?php
		/**
		 * Fire the action hook in header
		 */
		do_action( 'give_embed_head' );
		?>
	</head>
	<body class="give-form-templates">
		<?php
		$payment = new Payment( getPaymentId() );
		?>

		<div class="give-receipt-wrap give-embed-receipt">
			<div class="give-section receipt">
				<!-- <div class="image">
					<img src="https://external-content.duckduckgo.com/iu/?u=http%3A%2F%2F1.bp.blogspot.com%2F-aGWHh48vcl8%2FVNCI0Tz1OUI%2FAAAAAAAAAzM%2FREde4vFtO98%2Fs1600%2Fcheckbook-clipart-check-mark-hi.png&f=1&nofb=1" />
				</div> -->
				<div class="checkmark">
					<i class="fas fa-check"></i>
				</div>
				<h2 class="headline">
					A great big thank you!
				</h2>
				<p class="message">
					Your donation will go directly to saving the whales of our precious oceans. We’ve sent your donation receipt to <?php echo $payment->email; ?>
				</p>
				<div class="social-sharing">
					<p class="instruction">
						Tell the world about your generosity and help spread the word!
					</p>
					<div class="btn-row">
						<button class="give-btn social-btn facebook-btn">
							Share on Facebook<i class="fab fa-facebook"></i>
						</button>
						<button class="give-btn social-btn twitter-btn">
							Share on Twitter<i class="fab fa-twitter"></i>
						</button>
					</div>
				</div>
				<div class="details">
					<h3 class="headline">Donation Details</h3>

					<!-- Donor Details -->
					<div class="details-table">
						<div class="details-row">
							<i class="fas fa-user"></i>
							<div class="detail">
								Donor Name
							</div>
							<div class="value">
								<?php echo "{$payment->first_name} {$payment->last_name}"; ?>
							</div>
						</div>
						<div class="details-row">
							<i class="fas fa-envelope"></i>
							<div class="detail">
								Email Address
							</div>
							<div class="value">
							<?php echo $payment->email; ?>
							</div>
						</div>
						<?php if ( ! empty( $payment->address['line1'] ) ) : ?>
						<div class="details-row">
							<i class="fas fa-envelope"></i>
							<div class="detail">
								Billing Address
							</div>
							<div class="value">
								<?php echo $payment->address['line1']; ?> <br>
								<?php
								if ( ! empty( $payment->address['line2'] ) ) {
									echo $payment->address['line1'];
								}
								?>
								<?php echo $payment->address['city']; ?>, <?php echo $payment->address['state']; ?> <?php echo $payment->address['zip']; ?> <br>
								<?php echo $payment->address['country']; ?>
							</div>
						</div>
						<?php endif; ?>
					</div>

					<!-- Payment Details -->
					<div class="details-table">
						<div class="details-row">
							<div class="detail">
								Payment Method
							</div>
							<div class="value">
								<?php echo getGatewayLabel( $payment->gateway ); ?>
							</div>
						</div>
						<div class="details-row">
							<div class="detail">
								Donation Amount
							</div>
							<div class="value">
								<?php
								echo filterCurrency(
									sanitizeAmount( $payment->subtotal ),
									[
										'currency_code'   => $payment->currency,
										'decode_currency' => true,
										'form_id'         => $payment->form_id,
									]
								);
								?>
							</div>
						</div>
						<div class="details-row">
							<div class="detail">
								Processing Fees
							</div>
							<div class="value">
								<?php
								$fees = $payment->total - $payment->subtotal;
								echo filterCurrency(
									sanitizeAmount( $fees ),
									[
										'currency_code'   => $payment->currency,
										'decode_currency' => true,
										'form_id'         => $payment->form_id,
									]
								);
								?>
							</div>
						</div>
						<div class="details-row total">
							<div class="detail">
								Donation Total
							</div>
							<div class="value">
								<?php
								echo filterCurrency(
									sanitizeAmount( $payment->total ),
									[
										'currency_code'   => $payment->currency,
										'decode_currency' => true,
										'form_id'         => $payment->form_id,
									]
								);
								?>
							</div>
						</div>
					</div>
				</div>

				<!-- Download Receipt -->
				<button class="give-btn download-btn">
					Download Receipt <i class="fas fa-file-pdf"></i>
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

		/**
		 * Fire the action hook in footer
		 */
		do_action( 'give_embed_footer' );
		?>
	</body>
</html>
