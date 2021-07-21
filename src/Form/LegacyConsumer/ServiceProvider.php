<?php

namespace Give\Form\LegacyConsumer;

use Give\Helpers\Hooks;
use Give\Receipt\DonationReceipt;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Form\LegacyConsumer\Commands\DeprecateOldTemplateHook;

class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {
		include_once plugin_dir_path( __FILE__ ) . '/functions.php';
		give()->bind(
			DeprecateOldTemplateHook::class,
			function() {
				global $wp_filter;
				return new DeprecateOldTemplateHook( $wp_filter );
			}
		);
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {

		give( TemplateHooks::class )->walk( give( Commands\SetupNewTemplateHook::class ) );
		if ( ! wp_doing_ajax() ) {
			give( TemplateHooks::class )->walk( give( Commands\DeprecateOldTemplateHook::class ) );
		}

		add_filter(
			'give_donation_form_required_fields',
			function( $requiredFields, $formID ) {
				return give( TemplateHooks::class )->reduce( new Commands\SetupFieldValidation( $formID ), $requiredFields );
			},
			10,
			2
		);

		add_action(
			'give_insert_payment',
			function( $donationID, $donationData ) {
				give( TemplateHooks::class )->walk( new Commands\SetupFieldPersistance( $donationID, $donationData ) );
			},
			10,
			2
		);

		add_action(
			'give_new_receipt',
			function( DonationReceipt $receipt ) {
				give( TemplateHooks::class )->walk( new Commands\SetupFieldReceipt( $receipt ) );
			}
		);

		add_action(
			'give_payment_receipt_after',
			function( $payment, $receipt_args ) {
				give( TemplateHooks::class )->walk( new Commands\SetupFieldConfirmation( $payment, $receipt_args ) );
			},
			10,
			2
		);

		add_action(
			'give_add_email_tags',
			function() {
				give( TemplateHooks::class )->walk( new Commands\SetupFieldEmailTag );
			}
		);
	}
}
