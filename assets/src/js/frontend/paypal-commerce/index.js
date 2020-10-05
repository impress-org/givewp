/* globals Give, jQuery, givePayPalCommerce */
import DonationForm from './DonationForm';
import SmartButtons from './SmartButtons';
import AdvancedCardFields from './AdvancedCardFields';
import CustomCardFields from './CustomCardFields';
import { loadScript } from '@paypal/paypal-js';

document.addEventListener( 'DOMContentLoaded', () => {
	const $formWraps = document.querySelectorAll( '.give-form-wrap' );

	if ( ! $formWraps.length ) {
		return;
	}

	// Setup initial PayPal script on basis of first form on webpage.
	loadPayPalScript( $formWraps[ 0 ].querySelector( '.give-form' ) )
		.then( () => {
			setupPaymentMethods();
		} );

	$formWraps.forEach( $formWrap => {
		const $form = $formWrap.querySelector( '.give-form' );
		setRecurringFieldTrackerToReloadPaypalSDK( $form );
		setFormCurrencyTrackerToReloadPaypalSDK( $form );
		setupGatewayLoadEventToRenderPaymentMethods( $form );
	} );

	// On form submit prevent submission for PayPal commerce.
	// Form submission will be take care internally by smart buttons or advanced card fields.
	jQuery( 'form.give-form' ).on( 'submit', e => {
		if ( ! DonationForm.isPayPalCommerceSelected( jQuery( this ) ) ) {
			return true;
		}

		e.preventDefault();

		return false;
	} );

	/**
	 * Setup recurring field tracker to reload paypal sdk.
	 *
	 * @since 2.9.0
	 * @param {object} $form Form selector
	 */
	function setRecurringFieldTrackerToReloadPaypalSDK( $form ) {
		const recurringField = $form.querySelector( 'input[name="_give_is_donation_recurring"]' );

		if ( recurringField ) {
			DonationForm.trackRecurringHiddenFieldChange( recurringField, () => {
				loadPayPalScript( $form ).then( () => {
					setupPaymentMethods();
				} );
			} );
		}
	}

	/**
	 * Setup gateway load event to render payment methods.
	 *
	 * @since 2.9.0
	 * @param {object} $form Form selector
	 */
	function setupGatewayLoadEventToRenderPaymentMethods( $form ) {
		document.addEventListener( 'give_gateway_loaded', () => {
			if ( ! DonationForm.isPayPalCommerceSelected( jQuery( $form ) ) ) {
				return;
			}

			loadPayPalScript( $form ).then( () => {
				setupPaymentMethod( $form );
			} );
		} );
	}

	/**
	 * Setup form currency tracker to reload paypal sdk.
	 *
	 * @since 2.9.0
	 * @param {object} $form Form selector
	 */
	function setFormCurrencyTrackerToReloadPaypalSDK( $form ) {
		DonationForm.trackDonationCurrencyChange( $form, () => {
			loadPayPalScript( $form ).then( () => {
				setupPaymentMethods();
			} );
		} );
	}

	/**
	 * Setup PayPal payment methods
	 *
	 * @since 2.9.0
	 */
	function setupPaymentMethods() {
		$formWraps.forEach( $formWrap => {
			const $form = $formWrap.querySelector( '.give-form' );

			if ( ! DonationForm.isPayPalCommerceSelected( jQuery( $form ) ) ) {
				return;
			}

			setupPaymentMethod( $form );
		} );
	}

	/**
	 * Setup payment  method.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} $form Form selector
	 */
	function setupPaymentMethod( $form ) {
		const smartButtons = new SmartButtons( $form );
		const customCardFields = new CustomCardFields( $form );

		smartButtons.boot();

		// Boot CustomCardFields class before AdvancedCardFields because of internal dependencies.
		if ( AdvancedCardFields.canShow() ) {
			const advancedCardFields = new AdvancedCardFields( customCardFields );

			customCardFields.boot();
			advancedCardFields.boot();

			return;
		}

		customCardFields.removeFields();
	}

	/**
	 * Load PayPal script.
	 *
	 * @param {object} form Form selector
	 *
	 * @since 2.9.0
	 *
	 * @return {Promise}  PayPal sdk load promise.
	 */
	function loadPayPalScript( form ) {
		const options = {};
		const isRecurring = DonationForm.isRecurringDonation( form );
		// options.intent = isRecurring ? 'subscription' : 'capture';
		options.intent = 'capture';
		options.vault = !! isRecurring;
		options.currency = Give.form.fn.getInfo( 'currency_code', jQuery( form ) );

		return loadScript( { ...givePayPalCommerce.payPalSdkQueryParameters, ...options } );
	}
} );
