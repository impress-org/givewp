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
		const stripeElements = new GiveStripeElements( formElement );
		const setupStripeElement = stripeElements.setupStripeElement();
		const getStripeElements = stripeElements.getElements( setupStripeElement );
		let cardElements = stripeElements.createElement( getStripeElements, formElement );

		if ( 'stripe' === formGateway.value ) {
			stripeElements.mountElement(cardElements);
		}

		document.addEventListener( 'give_gateway_loaded', ( e ) => {
			const selectedGateway = e.detail.selectedGateway;
			const getStripeElements = stripeElements.getElements( setupStripeElement );
			cardElements = stripeElements.createElement( getStripeElements, formElement );

			if ( 'stripe' === selectedGateway ) {
				stripeElements.mountElement( cardElements );
			} else {
				stripeElements.unMountElement( cardElements );
			}
		});

		formElement.onsubmit = ( e ) => {
			const selectedGateway = formElement.querySelector( '.give-gateway:checked' ).value;

			// Bailout, if Stripe is not the selected gateway.
			if ( 'stripe' !== selectedGateway ) {
				return false;
			}

			// Create Payment Method.
			stripeElements.createPaymentMethod( formElement, setupStripeElement, cardElements );

			e.preventDefault();
		}
	} );
} );
