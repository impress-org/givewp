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

		/**
		 * Bailout, if Stripe publishable key does not found.
		 */
		if( ! formElement.getAttribute( 'data-publishable-key' ) ) {
			return;
		}

		const isUpdatingPaymentInfo = give_stripe_vars.hasOwnProperty( 'stripe_card_update' ) && parseInt( give_stripe_vars.stripe_card_update );
		const idPrefixElement = formElement.querySelector( 'input[name="give-form-id-prefix"]' );
		const stripeElements = new GiveStripeElements( formElement );
		const setupStripeElement = stripeElements.setupStripeElement();
		const getStripeElements = stripeElements.getElements( setupStripeElement );
		const cardElements = stripeElements.createElement( getStripeElements, formElement );
		const stripeCheckoutTypeHiddenField = Give.form.fn.getInfo( 'stripe-checkout-type', formElement );

		/**
		 * Returns the state of the
		 *
		 * @since 2.9.3
		 *
		 * @returns {{selectedGatewayId: string, formGateway: Element, isCheckoutTypeModal: boolean, isStripeModalCheckoutGateway: boolean}}
		 */
		function getFormState() {
			const formGateway = formElement.querySelector( 'input[name="give-gateway"]' );
			const selectedGatewayId = formGateway ? formGateway.value : '';
			const isCheckoutTypeModal = 'modal' === stripeCheckoutTypeHiddenField;

			return {
				formGateway,
				selectedGatewayId,
				isCheckoutTypeModal,
				isStripeModalCheckoutGateway: formGateway && 'stripe_checkout' === selectedGatewayId && isCheckoutTypeModal
			}
		}

		/**
		 * Mounts and unmounts the Stripe Elements to the form
		 *
		 * @since 2.9.3
		 *
		 * @param {boolean} doUnmount
		 */
		function mountStripeElements(doUnmount = true) {
			const { selectedGatewayId, isStripeModalCheckoutGateway } = getFormState();

			if ( isUpdatingPaymentInfo || 'stripe' === selectedGatewayId || isStripeModalCheckoutGateway ) {
				stripeElements.mountElement( cardElements );
			} else if( doUnmount ) {
				stripeElements.unMountElement( cardElements );
			}

			if ( isStripeModalCheckoutGateway ) {
				stripeElements.triggerStripeModal( formElement, stripeElements, setupStripeElement, cardElements );
			}
		}

		// Do initial mount
		mountStripeElements(false);

		// Mount & unmount when the users selects a gateway
		document.addEventListener( 'give_gateway_loaded', mountStripeElements );

		formElement.onsubmit = ( e ) => {
			const { selectedGatewayId, isStripeModalCheckoutGateway } = getFormState();

			// Bailout, if Stripe is not the selected gateway.
			if ( isUpdatingPaymentInfo || 'stripe' === selectedGatewayId ) {
				stripeElements.createPaymentMethod( formElement, setupStripeElement, cardElements );
				e.preventDefault();
			}

			if ( isStripeModalCheckoutGateway ) {
				const stripeModal = formElement.querySelector( '.give-stripe-checkout-modal' );
				const modalAmountElement = stripeModal.querySelector( '.give-stripe-checkout-donation-amount' );
				const modalEmailElement = stripeModal.querySelector( '.give-stripe-checkout-donor-email' );
				const donationAmount = formElement.querySelector( '.give-final-total-amount' ).textContent;
				const donorEmail = formElement.querySelector( 'input[name="give_email"]' ).value;
				const validatePaymentFields = formElement.querySelector( 'input[name="give_validate_stripe_payment_fields"]' );

				// Setup data on modal and then trigger to display modal.
				stripeModal.classList.add( 'give-stripe-checkout-show-modal' );
				null !== modalAmountElement ? modalAmountElement.innerHTML = donationAmount : '';
				null !== modalEmailElement ? modalEmailElement.innerHTML = donorEmail : '';
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
				e.preventDefault();
			}
		}
	} );
} );
