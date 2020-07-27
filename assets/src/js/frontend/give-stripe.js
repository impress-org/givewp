/* eslint-disable */
import {GiveStripeElements} from "./give-stripe-elements";

document.addEventListener( 'DOMContentLoaded', function( e ) {
	const formWraps = Array.from( document.querySelectorAll( '.give-form-wrap' ) );

	// Loop through the number of forms on the page.
	formWraps.forEach( formWrap => {
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
		const stripeElements = new GiveStripeElements( formElement );
		const setupStripeElement = stripeElements.setupStripeElement();
		const getStripeElements = stripeElements.getElements( setupStripeElement );
		let cardElements = stripeElements.createElement( getStripeElements, formElement );

		if ( 'stripe' === formGateway.value || 'stripe_checkout' === formGateway.value ) {
			stripeElements.mountElement(cardElements);
		}

		document.addEventListener( 'give_gateway_loaded', ( e ) => {
			const selectedGateway = e.detail.selectedGateway;
			const getStripeElements = stripeElements.getElements( setupStripeElement );
			cardElements = stripeElements.createElement( getStripeElements, formElement );

			if ( 'stripe' === selectedGateway || 'stripe_checkout' === selectedGateway ) {
				stripeElements.mountElement( cardElements );
			} else {
				stripeElements.unMountElement( cardElements );
			}
		});

		if ( 'stripe_checkout' === formGateway.value ) {
			const stripeModalDonateBtn = formElement.querySelector( '.give-stripe-checkout-modal-donate-button' );
			const cardholderName = formElement.querySelector( 'input[name="card_name"]' );
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

					// Create Payment Method.
					stripeElements.createPaymentMethod( formElement, setupStripeElement, cardElements );

					e.preventDefault();
				} );
			}

		}

		formElement.onsubmit = ( e ) => {
			const selectedGateway = formElement.querySelector( '.give-gateway:checked' ).value;

			// Bailout, if Stripe is not the selected gateway.
			if ( 'stripe' === selectedGateway ) {
				// Create Payment Method.
				stripeElements.createPaymentMethod( formElement, setupStripeElement, cardElements );
				// formElement.submit();

			} else if ( 'stripe_checkout' === selectedGateway ) {
				const stripeModal = formElement.querySelector( '.give-stripe-checkout-modal' );
				const modalAmountElement = stripeModal.querySelector( '.give-stripe-checkout-donation-amount' );
				const donationAmount = formElement.querySelector( '.give-final-total-amount' ).textContent;
				const validatePaymentFields = formElement.querySelector( 'input[name="give_validate_stripe_payment_fields"]' );

				// Setup data on modal and then trigger to display modal.
				stripeModal.classList.add( 'give-stripe-checkout-show-modal' );
				null !== modalAmountElement ? modalAmountElement.innerHTML = donationAmount : '';
				validatePaymentFields.setAttribute( 'value', '1' );

				const modalClose = formElement.querySelector( '.give-stripe-checkout-modal-close' );

				// Close Modal Popup.
				if ( null !== modalClose ) {
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
				}
			}

			e.preventDefault();
		}
	} );
} );
