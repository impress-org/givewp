<?php

namespace Give\Form\LegacyConsumer;

use Give\Receipt\DonationReceipt;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Form\LegacyConsumer\Commands\DeprecateOldTemplateHook;
use Give_Donate_Form;

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

		give()->singleton( UniqueIdAttributeGenerator::class );
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {

		give( TemplateHooks::class )->walk( give( Commands\SetupNewTemplateHook::class ) );
		if ( ! wp_doing_ajax() ) {
			give( TemplateHooks::class )->walk( give( Commands\DeprecateOldTemplateHook::class ) );
		}

		add_action(
			'give_checkout_error_checks',
			function() {
				$formId = absint( $_POST['give-form-id'] );
				give( TemplateHooks::class )->walk( new Commands\SetupFieldValidation( $formId ) );
			}
		);

		add_action(
			'give_form_html_tags',
			/**
			 * @since 2.14.0
			 * @param array $formHtmlAttributes
			 * @param Give_Donate_Form $form
			 *
			 * @return void
			 */
			function( $formHtmlAttributes, $form ) {
				return give( TemplateHooks::class )->reduce( new AddEnctypeAttributeInDonationForm( $form->ID ), $formHtmlAttributes );
			},
			10,
			 2
		);

		add_action(
			'give_insert_payment',
			function( $donationID, $donationData ) {
				give( TemplateHooks::class )->walk( new Commands\SetupFieldPersistence( $donationID, $donationData ) );
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
