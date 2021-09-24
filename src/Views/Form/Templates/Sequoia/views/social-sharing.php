<?php if ( isset( $options['thank-you']['sharing'] ) && $options['thank-you']['sharing'] === 'enabled' ) : ?>
	<div class="social-sharing">
		<p class="instruction">
			<?php echo esc_html( $options['thank-you']['sharing_instruction'] ); ?>
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
				<?php esc_html_e( 'Share on Facebook', 'give' ); ?><i class="fab fa-facebook"></i>
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
					const text = `<?php echo $options['thank-you']['twitter_message']; ?>`;
					// Open new window with prompt for Twitter sharing
					window.Give.share.fn.twitter(url, text);
					return false;
					">
				<?php esc_html_e( 'Share on Twitter', 'give' ); ?><i class="fab fa-twitter"></i>
			</button>
		</div>
	</div>
<?php endif; ?>
