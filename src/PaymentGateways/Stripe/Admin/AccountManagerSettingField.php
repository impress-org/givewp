<?php

namespace Give\PaymentGateways\Stripe\Admin;

/**
 * Class AccountManagerSettingField
 * @package Give\PaymentGateways\Stripe\Admin
 * @unreleased
 */
class AccountManagerSettingField {
	/**
	 * Render Stripe account manager setting field.
	 *
	 * @unreleased
	 *
	 * @param array $field
	 */
	public function handle( $field ) {
		$stripe_accounts = give_stripe_get_all_accounts();
		$default_account = give_stripe_get_default_account_slug();

		// Set account as default.
		if ( 1 === count( $stripe_accounts ) ) {
			$stripe_account_keys = array_keys( $stripe_accounts );
			$default_account     = $stripe_account_keys[0];
		}

		$site_url            = get_site_url();
		$modal_title         = sprintf(
			'<strong>%1$s</strong>',
			esc_html__( 'You are connected! Now this is important: Please configure your Stripe webhook to finalize the setup.', 'give' )
		);
		$modal_first_detail  = sprintf(
			'%1$s %2$s',
			esc_html__( 'In order for Stripe to function properly, you must add a new Stripe webhook endpoint. To do this please visit the <a href=\'https://dashboard.stripe.com/webhooks\' target=\'_blank\'>Webhooks Section of your Stripe Dashboard</a> and click the <strong>Add endpoint</strong> button and paste the following URL:', 'give' ),
			"<strong>{$site_url}?give-listener=stripe</strong>"
		);
		$modal_second_detail = esc_html__( 'Stripe webhooks are required so GiveWP can communicate properly with the payment gateway to confirm payment completion, renewals, and more.', 'give' );
		$can_display         = ! empty( $_GET['stripe_account'] ) ? '0' : '1';
		?>
		<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . esc_attr( $field['wrapper_class'] ) . '"' : ''; ?>>
			<td class="give-forminp give-forminp-api_key">
				<?php include  GIVE_PLUGIN_DIR . '/src/PaymentGateways/Stripe/resources/views/account-manager-setting-field/index.php'; ?>
			</td>
		</tr>
		<?php
	}
}
