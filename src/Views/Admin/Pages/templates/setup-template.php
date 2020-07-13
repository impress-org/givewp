<div class="wrap" class="give-setup-page">

	<h1 class="wp-heading-inline">
		<?php echo __( 'Setup GiveWP', 'give' ); ?>
	</h1>

	<hr class="wp-header-end">

	<section class="section">
		<h2 class="section-header"><?php echo esc_html_e( 'Create Your First Donation Form in Minutes', 'give' ); ?></h2>

		<!-- Onboarding Wizard -->
		<?php
		$this->render_template(
			'setup-step-template',
			[
				'dashicon'   => 'admin-settings',
				'labelText'  => esc_html__( 'First-Time Configuration', 'give' ),
				'actionText' => esc_html__( 'Get Started', 'give' ),
			]
		);
		?>

	</section>

	<section class="section">
		<h2 class="section-header"><?php esc_html_e( 'Connect a Payment Gateway to Accept Donations', 'give' ); ?></h2>

		<!-- PayPal Checkout -->
		<?php
		$this->render_template(
			'setup-step-template',
			[
				'dashicon'   => 'admin-generic',
				'labelText'  => esc_html__( 'PayPal Checkout', 'give' ),
				'actionText' => esc_html__( 'Connect to PayPal', 'give' ),
			]
		);
		?>

		<!-- Stripe -->
		<?php
		$this->render_template(
			'setup-step-template',
			[
				'dashicon'   => 'admin-generic',
				'labelText'  => esc_html__( 'Stripe', 'give' ),
				'actionText' => esc_html__( 'Connect to Stripe', 'give' ),
			]
		);
		?>

	</section>

	<section class="section">
		<h2 class="section-header"><?php esc_html_e( 'Level Up Your Fundraising', 'give' ); ?></h2>

		<!-- Give 101 -->
		<?php
		$this->render_template(
			'setup-step-template',
			[
				'dashicon'       => 'welcome-learn-more',
				'labelText'      => esc_html__( 'GiveWP 101', 'give' ),
				'actionText'     => esc_html__( 'View GiveWP 101', 'give' ),
				'actionLocation' => esc_url( 'https://givewp.com/documentation/' ),
			]
		);
		?>

		<!-- Add-ons -->
		<?php
		$this->render_template(
			'setup-step-template',
			[
				'dashicon'       => 'admin-plugins',
				'labelText'      => esc_html__( 'GiveWP Premium Add-ons', 'give' ),
				'actionText'     => esc_html__( 'View Add-ons', 'give' ),
				'actionLocation' => esc_url( 'https://givewp.com/addons/' ),
			]
		);
		?>

	</section>

	<div class="section-dismiss">
		<form action="<?php echo admin_url( 'admin-post.php' ); ?>">
			<input type="hidden" name="action" value="dismiss_setup_page">
			<?php wp_nonce_field( 'dismiss_setup_page' ); ?>
			<button type="submit"><?php echo esc_html__( 'Dismiss Setup Screen', 'give' ); ?></button>
		</form>
	</div>

</div>
