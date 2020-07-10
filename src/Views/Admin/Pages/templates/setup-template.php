<div class="wrap" class="give-setup-page">

	<h1 class="wp-heading-inline">
		<?php echo __( 'Setup GiveWP', 'give' ); ?>
	</h1>

	<hr class="wp-header-end">

	<!--
	<div class="error">
		&nbsp;
	</div>
	-->

	<section class="section">
		<h2 class="section-header">Create Your First Donation Form in Minutes</h2>

		<!-- Onboarding Wizard -->
		<?php
		$this->render_template(
			'setup-step-template',
			[
				'dashicon'   => 'admin-settings',
				'labelText'  => 'First-Time Configuration',
				'actionText' => 'Get Started',
			]
		);
		?>

	</section>

	<section class="section">
		<h2 class="section-header">Connect a Payment Gateway to Accept Donations</h2>

		<!-- PayPal Checkout -->
		<?php
		$this->render_template(
			'setup-step-template',
			[
				'dashicon'   => 'admin-generic',
				'labelText'  => 'PayPal Checkout',
				'actionText' => 'Connect to PayPal',
			]
		);
		?>

		<!-- Stripe -->
		<?php
		$this->render_template(
			'setup-step-template',
			[
				'dashicon'   => 'admin-generic',
				'labelText'  => 'Stripe',
				'actionText' => 'Connect to Stripe',
			]
		);
		?>

	</section>

	<section class="section">
		<h2 class="section-header">Level Up Your Fundraising</h2>

		<!-- Give 101 -->
		<?php
		$this->render_template(
			'setup-step-template',
			[
				'dashicon'       => 'welcome-learn-more',
				'labelText'      => 'GiveWP 101',
				'actionText'     => 'View GiveWP 101',
				'actionLocation' => 'https://givewp.com/documentation/',
			]
		);
		?>

		<!-- Add-ons -->
		<?php
		$this->render_template(
			'setup-step-template',
			[
				'dashicon'       => 'admin-plugins',
				'labelText'      => 'GiveWP Premium Add-ons',
				'actionText'     => 'View Add-ons',
				'actionLocation' => 'https://givewp.com/addons/',
			]
		);
		?>

	</section>

	<footer style="text-align:center;">
		<form action="<?php echo admin_url( 'admin-post.php' ); ?>">
			<input type="hidden" name="action" value="dismiss_setup_page">
			<?php wp_nonce_field( 'dismiss_setup_page' ); ?>
			<button type="submit">Dismiss Setup Screen</button>
		</form>
	</footer>

</div>
