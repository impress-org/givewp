<div id="give-stripe-account-manager-description">
	<h2><?php
		esc_html_e( 'Manage Your Stripe Accounts', 'give' ); ?></h2>
	<p class="give-field-description">
		<?php
		esc_html_e( 'Connect to the Stripe payment gateway using this section. Multiple Stripe accounts can be connected simultaneously. All donation forms will use the "Default Account" unless configured otherwise. To specify a different Stripe account for a form, configure the settings within the "Stripe Account" tab on the individual form edit screen.', 'give' );
		?>
	</p>
	<?php
	if ( ! give_stripe_is_premium_active() ) :
		?>
		<p class="give-field-description">
			<br/>
			<?php
			echo sprintf(
				__( 'NOTE: You are using the free Stripe payment gateway integration. This includes an additional 2%% fee for processing one-time donations. This fee is removed by activating the premium <a href="%1$s" target="_blank">Stripe add-on</a> and never applies to subscription donations made through the <a href="%2$s" target="_blank">Recurring Donations add-on</a>. <a href="%3$s" target="_blank">Learn More ></a>', 'give' ),
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
