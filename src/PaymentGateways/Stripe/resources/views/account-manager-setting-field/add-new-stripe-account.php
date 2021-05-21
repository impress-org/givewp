<?php
if ( ! give_has_upgrade_completed( 'v270_store_stripe_account_for_donation' ) ) : ?>
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
	?>
	<?php
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
				 * @param  array  $stripe_accounts  All Stripe accounts.
				 *
				 */
				do_action( 'give_stripe_premium_manual_api_fields', $stripe_accounts );
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
