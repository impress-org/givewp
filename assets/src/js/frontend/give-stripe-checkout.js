import { GiveStripeElements } from './give-stripe-elements';

document.addEventListener( 'DOMContentLoaded', ( evt ) => {
	const stripe_handler = [];
	const formWraps = document.querySelectorAll( '.give-form-wrap' );

	// Loop through the number of forms on the page.
	Array.prototype.forEach.call( formWraps, function( formWrap ) {
		const formElement = formWrap.querySelector( '.give-form' );

		/**
		 * Bailout, if `form_element` is null.
		 *
		 * We are bailing out here as this script is loaded on every page of the
		 * site but the `form_element` only exists on the pages when Give donation
		 * form is loaded. So, when the pages where the donation form is not loaded
		 * will show console error. To avoid JS console errors we bail it, if
		 * `form_element` is null to avoid console errors.
		 */
		if ( null === formElement ) {
			return;
		}

		const modalBtn = formElement.querySelector( '.give-stripe-checkout-modal-btn' );
		const publishableKey = formElement.getAttribute( 'data-publishable-key' );
		const accountId = formElement.getAttribute( 'data-account' );
		const formName = null !== formElement.querySelector( 'input[name="give-form-title"]' ) ?
			formElement.querySelector( 'input[name="give-form-title"]' ).value :
			false;
		const idPrefix = null !== formElement.querySelector( 'input[name="give-form-id-prefix"]' ) ?
			formElement.querySelector( 'input[name="give-form-id-prefix"]' ).value :
			false;
		const checkoutImage = ( give_stripe_vars.checkout_image.length > 0 ) ? give_stripe_vars.checkout_image : '';
		const checkoutAddress = ( give_stripe_vars.checkout_address.length > 0 );
		const isZipCode = ( give_stripe_vars.zipcode_option.length > 0 );
		const isRememberMe = ( give_stripe_vars.remember_option.length > 0 );
		const siteTitle = give_stripe_vars.sitename;
		const btnTitle = give_stripe_vars.checkoutBtnTitle;

		/**
		 * Bailout, when publishable key is not present for a donation form
		 * due to Stripe account not properly attached to the form or global
		 * Stripe account is not added.
		 */
		if ( null === publishableKey ) {
			return;
		}

		const stripeModal = formElement.querySelector( '.give-stripe-checkout-modal' );
		const stripeModalDonateBtn = formElement.querySelector( '.give-stripe-checkout-modal-donate-button' );
		const cardholderName = formElement.querySelector( 'input[name="card_name"]' );
		const formGateway = formElement.querySelector( 'input[name="give-gateway"]' );
		const gateways = Array.from( formElement.querySelectorAll( '.give-gateway' ) );

		const stripeElements = new GiveStripeElements( formElement );
		const cardElements = stripeElements.createElement( stripeElements.getElements( stripeElements.setupStripeElement() ) );

		if ( formGateway && 'stripe_checkout' === formGateway.value ) {
			stripeElements.mountElement( cardElements );
		}

		const completeCardElements = {};
		let completeCardStatus = false;

		cardElements.forEach( ( cardElement ) => {
			completeCardElements.cardName = false;
			cardElement.on( 'ready', ( e ) => {
				completeCardElements[ e.elementType ] = false;
			} );

			cardElement.on( 'change', ( e ) => {
				completeCardElements[ e.elementType ] = e.complete;
				completeCardStatus = Object.values( completeCardElements ).every( ( string ) => {
					return true === string;
				} );
				completeCardStatus ? stripeModalDonateBtn.removeAttribute( 'disabled' ) : stripeModalDonateBtn.setAttribute( 'disabled', 'disabled' );
			} );
		} );

		if ( null !== cardholderName ) {
			cardholderName.addEventListener( 'keyup', ( e ) => {
				completeCardElements.cardName = '' !== e.target.value;
				completeCardStatus = Object.values( completeCardElements ).every( ( string ) => {
					return true === string;
				} );
				completeCardStatus ? stripeModalDonateBtn.removeAttribute( 'disabled' ) : stripeModalDonateBtn.setAttribute( 'disabled', 'disabled' );
			} );
		}

		stripeModalDonateBtn.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			const mainDonateBtn = formElement.querySelector( 'input[name="give-purchase"]' );
			mainDonateBtn.removeAttribute( 'disabled' );
			mainDonateBtn.click();
		} );

		formElement.onsubmit = function( evt ) {
			const selectedGateway = formElement.querySelector( '.give-gateway:checked' ).value;
			const formName = null !== formElement.querySelector( 'input[name="give-form-title"]' ) ?
				formElement.querySelector( 'input[name="give-form-title"]' ).value :
				false;
			const donorEmail = formElement.querySelector( 'input[name="give_email"]' ).value;
			const idPrefix = formElement.querySelector( 'input[name="give-form-id-prefix"]' ).value;
			const donationAmount = formElement.querySelector( '.give-final-total-amount' ).textContent;
			const validatePaymentFields = formElement.querySelector( 'input[name="give_validate_stripe_payment_fields"]' );

			stripeModal.classList.add( 'give-stripe-checkout-show-modal' );
			validatePaymentFields.setAttribute( 'value', '1' );

			// new GiveStripeModal(
			// 	{
			// 		modalWrapper: 'give-modal--stripe-checkout',
			// 		modalContent: {
			// 			title: siteTitle,
			// 			price: donationAmount,
			// 			email: donorEmail,
			// 			formTitle: formName,
			// 			btnTitle: btnTitle,
			// 			formElement: formElement,
			// 			idPrefix: idPrefix,
			// 		},
			// 	}
			// ).render();

			evt.preventDefault();
		};
	} );

	/**
	 * Format Stripe Currency
	 *
	 * @param {number} amount       Donation Amount.
	 * @param {object} form_element Donation Form Element.
	 *
	 * @returns {number}
	 */
	function give_stripe_format_currency( amount, form_element ) {
		let formattedCurrency = Math.abs( parseFloat( accounting.unformat( amount, Give.form.fn.getInfo( 'decimal_separator', jQuery( form_element ) ) ) ) );
		const selectedCurrency = form_element.getAttribute( 'data-currency_code' );
		const zeroDecimalCurrencies = give_stripe_vars.zero_based_currencies_list;

		// If not Zero Decimal Based Currency, then multiply with 100.
		if ( zeroDecimalCurrencies.indexOf( selectedCurrency ) < 0 ) {
			formattedCurrency = formattedCurrency * 100;
		}

		return formattedCurrency;
	}

	/**
	 * This function is used to reset the donate button.
	 *
	 * @param form_element
	 */
	function give_stripe_refresh_donate_button( form_element ) {
		const $donate_button_wrap = form_element.querySelector( '.give-submit-button-wrap' );
		const $donate_button = $donate_button_wrap.querySelector( '#give-purchase-button' );

		// Remove loading animations.
		$donate_button_wrap.querySelector( '.give-loading-animation' ).style.display = 'none';

		// Refresh donate button.
		$donate_button.value = $donate_button.getAttribute( 'data-before-validation-label' );
		$donate_button.removeAttribute( 'disabled' );
	}
} );
