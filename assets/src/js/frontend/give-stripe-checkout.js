import { GiveStripeElements } from './give-stripe-elements';

document.addEventListener( 'DOMContentLoaded', ( evt ) => {
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

		const formGateway = formElement.querySelector( 'input[name="give-gateway"]' );
		const idPrefixElement = formElement.querySelector( 'input[name="give-form-id-prefix"]' );
		const stripeModalDonateBtn = formElement.querySelector( '.give-stripe-checkout-modal-donate-button' );
		const cardholderName = formElement.querySelector( 'input[name="card_name"]' );
		const stripeElements = new GiveStripeElements( formElement );
		const setupStripeElement = stripeElements.setupStripeElement();
		const getStripeElements = stripeElements.getElements( setupStripeElement );
		let cardElements = stripeElements.createElement( getStripeElements, formElement );
		const completeCardElements = {};
		let completeCardStatus = false;

		if ( 'stripe_checkout' === formGateway.value ) {
			stripeElements.mountElement( cardElements );
		}

		document.addEventListener( 'give_gateway_loaded', ( e ) => {
			const selectedGateway = e.detail.selectedGateway;
			const getStripeElements = stripeElements.getElements( setupStripeElement );
			cardElements = stripeElements.createElement( getStripeElements, formElement );

			if ( 'stripe_checkout' === selectedGateway ) {
				stripeElements.mountElement( cardElements );
			} else {
				stripeElements.unMountElement( cardElements );
			}
		} );

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

		if ( null !== stripeModalDonateBtn ) {
			// Process donation on the click of the modal donate button.
			stripeModalDonateBtn.addEventListener( 'click', ( e ) => {
				const currentModalDonateBtn = e.target;
				const loadingAnimationElement = currentModalDonateBtn.nextElementSibling;

				// Show Loading Icon on submitting modal donate btn.
				currentModalDonateBtn.value = '';
				loadingAnimationElement.classList.add( 'sequoia-loader' );
				loadingAnimationElement.classList.add( 'spinning' );
				loadingAnimationElement.classList.remove( 'give-loading-animation' );

				const billing_details = {
					name: '',
					email: '',
					address: {
						line1: '',
						line2: '',
						city: '',
						state: '',
						postal_code: '',
						country: '',
					},
				};
				const firstName = formElement.querySelector( 'input[name="give_first"]' ).value;
				const lastName = formElement.querySelector( 'input[name="give_last"]' ).value;
				const email = formElement.querySelector( 'input[name="give_email"]' ).value;
				const formSubmit = formElement.querySelector( '[id^=give-purchase-button]' );

				// Disable the submit button to prevent repeated clicks.
				formSubmit.setAttribute( 'disabled', 'disabled' );

				billing_details.name = `${ firstName } ${ lastName }`;
				billing_details.email = email;

				// Gather additional customer data we may have collected in our form.
				if ( give_stripe_vars.checkout_address && ! give_stripe_vars.stripe_card_update ) {
					const address1 = formElement.querySelector( '.card-address' ).value;
					const address2 = formElement.querySelector( '.card-address-2' ).value;
					const city = formElement.querySelector( '.card-city' ).value;
					const state = formElement.querySelector( '.card_state' ).value;
					const zip = formElement.querySelector( '.card-zip' ).value;
					const country = formElement.querySelector( '.billing-country' ).value;

					billing_details.address.line1 = address1 ? address1 : '';
					billing_details.address.line2 = address2 ? address2 : '';
					billing_details.address.city = city ? city : '';
					billing_details.address.state = state ? state : '';
					billing_details.address.postal_code = zip ? zip : '';
					billing_details.address.country = country ? country : '';
				}

				// Create Payment Method using the CC fields.
				setupStripeElement.createPaymentMethod( {
					type: 'card',
					card: cardElements[ 0 ],
					billing_details: billing_details,
				} ).then( function( result ) {
					if ( result.error ) {
						const donateBtn = formElement.getElementById( 'give-purchase-button' );
						const error = `<div class="give_errors"><p class="give_error">${ result.error.message }</p></div>`;

						// re-enable the submit button.
						donateBtn.setAttribute( 'disabled', false );

						// Display Error on the form.
						formElement.getElementById( `give-stripe-payment-errors-${ formId }` ).innerHTML = error;

						// Reset Donate Button.
						if ( give_global_vars.complete_purchase ) {
							formElement.value = give_global_vars.complete_purchase;
						} else {
							formElement.value = formElement.getAttribute( 'data-before-validation-label' );
						}
					} else {
						formElement.querySelector( 'input[name="give_stripe_payment_method"]' ).value = result.paymentMethod.id;
						formElement.submit();
					}
				} );
				e.preventDefault();
			} );
		}

		// This will be triggered when the actual donation form is submitted.
		formElement.onsubmit = function( evt ) {
			const selectedGateway = formElement.querySelector( '.give-gateway:checked' ).value;

			// Bailout, if Stripe Checkout is not the selected gateway.
			if ( 'stripe_checkout' !== selectedGateway ) {
				return false;
			}

			const stripeModal = formElement.querySelector( '.give-stripe-checkout-modal' );
			const modalAmountElement = stripeModal.querySelector( '.give-stripe-checkout-donation-amount' );
			const donationAmount = formElement.querySelector( '.give-final-total-amount' ).textContent;
			const validatePaymentFields = formElement.querySelector( 'input[name="give_validate_stripe_payment_fields"]' );

			// Setup data on modal and then trigger to display modal.
			stripeModal.classList.add( 'give-stripe-checkout-show-modal' );
			null !== modalAmountElement ? modalAmountElement.innerHTML = donationAmount : '';
			validatePaymentFields.setAttribute( 'value', '1' );

			evt.preventDefault();
		};

		const modalClose = formElement.querySelector( '.give-stripe-checkout-modal-close' );

		// Close Modal Popup.
		modalClose.addEventListener( 'click', ( e ) => {
			formElement.querySelector( `#give-stripe-checkout-modal-${ idPrefixElement.value }` ).classList.remove( 'give-stripe-checkout-show-modal' );
			const mainDonateBtn = formElement.querySelector( '.give-submit' );

			if ( null !== mainDonateBtn ) {
				mainDonateBtn.value = mainDonateBtn.getAttribute( 'data-before-validation-label' );
				mainDonateBtn.nextElementSibling.style.display = 'none';
				mainDonateBtn.removeAttribute( 'disabled' );
				formElement.querySelector( 'input[name="give_validate_stripe_payment_fields"]' ).setAttribute( 'value', '0' );
			}
			e.preventDefault();
		} );
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
