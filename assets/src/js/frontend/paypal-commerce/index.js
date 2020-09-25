/* globals jQuery, givePayPalCommerce */
import DonationForm from './DonationForm';
import SmartButtons from './SmartButtons';
import AdvancedCardFields from './AdvancedCardFields';
import CustomCardFields from './CustomCardFields';
import { loadScript } from '@paypal/paypal-js';

document.addEventListener( 'DOMContentLoaded', () => {
	/**
	 * Setup PayPal payment methods
	 *
	 * @since 2.9.0
	 */
	function setupPaymentMethods() {
		const $formWraps = document.querySelectorAll( '.give-form-wrap' );

		if ( ! $formWraps.length ) {
			return;
		}

		$formWraps.forEach( $formWrap => {
			const $form = $formWrap.querySelector( '.give-form' );
			const smartButtons = new SmartButtons( $form );
			const customCardFields = new CustomCardFields( $form );

			smartButtons.boot();

			// Boot CustomCardFields class before AdvancedCardFields because of internal dependencies.
			if ( AdvancedCardFields.canShow() ) {
				const advancedCardFields = new AdvancedCardFields( customCardFields );

				customCardFields.boot();
				advancedCardFields.boot();
			} else {
				if ( DonationForm.isPayPalCommerceSelected( jQuery( $form ) ) ) {
					customCardFields.removeFields();
				}

				customCardFields.removeFieldsOnGatewayLoad();
			}
		} );
	}

	/**
	 * Load PayPal script.
	 *
	 * @since 2.9.0
	 */
	function loadPayPalScript() {
		loadScript( givePayPalCommerce.payPalSdkQueryParameters ).then( () => {
			setupPaymentMethods();
		} );
	}

	loadPayPalScript();

	// On form submit prevent submission for PayPal commerce.
	// Form submission will be take care internally by smart buttons or advanced card fields.
	jQuery( 'form.give-form' ).on( 'submit', e => {
		if ( ! DonationForm.isPayPalCommerceSelected( jQuery( this ) ) ) {
			return true;
		}

		e.preventDefault();

		return false;
	} );
} );
