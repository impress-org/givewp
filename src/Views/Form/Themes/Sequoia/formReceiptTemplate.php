<!DOCTYPE html>
<html lang="en" style="margin-top: 0 !important;">
	<head>
		<meta charset="utf-8">
		<title><?php _e( 'TESTINGG', 'give' ); ?></title>

		<?php
		/**
		 * Fire the action hook in header
		 */
		do_action( 'give_embed_head' );
		?>
	</head>
	<body class="give-form-templates">
		<?php
		global $post;
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
					Your donation will go directly to saving the whales of our precious oceans. We’ve sent your donation receipt to ben@gmail.com
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
								Ben Smith
							</div>
						</div>
						<div class="details-row">
							<i class="fas fa-envelope"></i>
							<div class="detail">
								Email Address
							</div>
							<div class="value">
								bensmith@gmail.com
							</div>
						</div>
						<div class="details-row">
							<i class="fas fa-envelope"></i>
							<div class="detail">
								Billing Address
							</div>
							<div class="value">
								875 26th Street <br>
								San Diego, CA 92021 <br>
								USA
							</div>
						</div>
					</div>

					<!-- Payment Details -->
					<div class="details-table">
						<div class="details-row">
							<div class="detail">
								Payment Method
							</div>
							<div class="value">
								Credit Card
							</div>
						</div>
						<div class="details-row">
							<div class="detail">
								Donation Amount
							</div>
							<div class="value">
								$50
							</div>
						</div>
						<div class="details-row">
							<div class="detail">
								Processing Fees
							</div>
							<div class="value">
								$5
							</div>
						</div>
						<div class="details-row total">
							<div class="detail">
								Donation Total
							</div>
							<div class="value">
								$55
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
