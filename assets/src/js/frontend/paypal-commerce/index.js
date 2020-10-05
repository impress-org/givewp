/* globals Give, jQuery, givePayPalCommerce */
import DonationForm from './DonationForm';
import SmartButtons from './SmartButtons';
import AdvancedCardFields from './AdvancedCardFields';
import CustomCardFields from './CustomCardFields';
import { loadScript } from '@paypal/paypal-js';

document.addEventListener( 'DOMContentLoaded', () => {
	/**
	 * Setup recurring field tracker to reload paypal sdk.
	 *
	 * @since 2.9.0
	 * @param {object} $formWraps Form container selectors
	 */
	function setRecurringFieldTrackerToReloadPaypalSDK( $formWraps ) {
		$formWraps.forEach( $formWrap => {
			const $form = $formWrap.querySelector( '.give-form' );
			const recurringField = $form.querySelector( 'input[name="_give_is_donation_recurring"]' );

			if ( recurringField ) {
				DonationForm.trackRecurringHiddenFieldChange( $form.querySelector( 'input[name="_give_is_donation_recurring"]' ), () => {
					loadPayPalScript( $form );
				} );
			}
		} );
	}

	/**
	 * Setup gateway load event to render payment methods.
	 *
	 * @since 2.9.0
	 * @param {object} $formWraps Form container selectors
	 */
	function setupGatewayLoadEventToRenderPaymentMethods( $formWraps ) {
		document.addEventListener( 'give_gateway_loaded', () => {
			$formWraps.forEach( $formWrap => {
				const $form = $formWrap.querySelector( '.give-form' );

				if ( ! DonationForm.isPayPalCommerceSelected( jQuery( $form ) ) ) {
					return;
				}

				const smartButtons = new SmartButtons( $form );
				const customCardFields = new CustomCardFields( $form );

				smartButtons.boot();

				// Boot CustomCardFields class before AdvancedCardFields because of internal dependencies.
				if ( AdvancedCardFields.canShow() ) {
					const advancedCardFields = new AdvancedCardFields( customCardFields );

					customCardFields.boot();
					advancedCardFields.boot();
				}
			} );
		} );
	}

	/**
	 * Setup form currency tracker to reload paypal sdk.
	 *
	 * @since 2.9.0
	 * @param {object} $formWraps Form container selectors
	 */
	function setFormCurrencyTrackerToReloadPaypalSDK( $formWraps ) {
		$formWraps.forEach( $formWrap => {
			const $form = $formWrap.querySelector( '.give-form' );
			DonationForm.trackDonationCurrencyChange( $form, () => {
				loadPayPalScript( $form );
			} );
		} );
	}

	/**
	 * Setup PayPal payment methods
	 *
	 * @since 2.9.0
	 * @param {object} $formWraps Form container selectors
	 */
	function setupPaymentMethods( $formWraps ) {
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
		} else {
			if ( DonationForm.isPayPalCommerceSelected( jQuery( $form ) ) ) {
				customCardFields.removeFields();
			}

			customCardFields.removeFieldsOnGatewayLoad();
		}
	}

	/**
	 * Load PayPal script.
	 *
	 * @param {object} form Form selector
	 *
	 * @since 2.9.0
	 */
	function loadPayPalScript( form ) {
		const options = {};
		const isRecurring = DonationForm.isRecurringDonation( form );
		// options.intent = isRecurring ? 'subscription' : 'capture';
		options.intent = 'capture';
		options.vault = !! isRecurring;
		options.currency = Give.form.fn.getInfo( 'currency_code', jQuery( form ) );

		loadScript( { ...givePayPalCommerce.payPalSdkQueryParameters, ...options } ).then( () => {
			setupPaymentMethods( $formWraps );
		} );
	}

	const $formWraps = document.querySelectorAll( '.give-form-wrap' );
	if ( $formWraps.length ) {
		loadPayPalScript( $formWraps[ 0 ].querySelector( '.give-form' ) );
		setRecurringFieldTrackerToReloadPaypalSDK( $formWraps );
		setFormCurrencyTrackerToReloadPaypalSDK( $formWraps );
		setupGatewayLoadEventToRenderPaymentMethods( $formWraps );
	}

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
