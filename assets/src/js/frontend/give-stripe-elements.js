/**
 * Give - Stripe Elements.
 *
 * @since 2.8.0
 */
class GiveStripeElements {
	/**
	 * Stripe Elements constructor.
	 *
	 * @param formElement
	 *
	 * @since 2.8.0
	 */
	constructor( formElement ) {
		// Don't load JS if `formElement` is not present.
		if ( ! formElement ) {
			return;
		}

		// Setup configuration.
		this.formElement = formElement;
		this.publishableKey = formElement.getAttribute( 'data-publishable-key' );
		this.accountId = formElement.getAttribute( 'data-account' ) ? formElement.getAttribute( 'data-account' ) : '';
		this.idPrefix = formElement.getAttribute( 'data-id' ) ? formElement.getAttribute( 'data-id' ) : '';
		this.locale = give_stripe_vars.preferred_locale;
		this.fieldsFormat = give_stripe_vars.cc_fields_format;
		this.isSingleInputField = this.fieldsFormat === 'single';
		this.isMounted = false;
		this.fontStyles = [];

		// If font styles are defined, add them to font styles array
		if ( Object.keys( give_stripe_vars.element_font_styles ).length !== 0 ) {
			this.fontStyles.push( give_stripe_vars.element_font_styles );
		}
	}

	/**
	 * Setup Stripe Element.
	 *
	 * @since 2.8.0
	 *
	 * @returns {*}
	 */
	setupStripeElement() {
		let args = {};

		if ( this.accountId.trim().length !== 0 ) {
			args = {
				stripeAccount: this.accountId,
			};
		}

		return Stripe( this.publishableKey, args );
	}

	/**
	 * Get Stripe Elements.
	 *
	 * @param stripeElement
	 *
	 * @since 2.8.0
	 *
	 * @returns {*}
	 */
	getElements( stripeElement ) {
		let args = {
			locale: this.locale,
		};

		// Add Fonts to Stripe Elements.
		if ( this.fontStyles.length > 0 ) {
			args = {
				fonts: this.fontStyles,
				locale: this.locale,
			};
		}

		return stripeElement.elements( args );
	}

	/**
	 * Create Card Element.
	 *
	 * @param stripeElement
	 * @param formElement
	 *
	 * @since 2.8.0
	 *
	 * @returns {[]}
	 */
	createElement( stripeElement, formElement ) {
		const paymentElement = [];
		const mountOnElements = this.getElementsToMountOn();
		const args = {
			style: this.getElementStyles(),
			classes: this.getElementClasses(),
		};

		mountOnElements.forEach( ( element, index ) => {
			if ( 'card' === element[ 0 ] ) {
				args.hidePostalCode = !! ( give_stripe_vars.checkout_address );
			} else if ( 'cardNumber' === element[ 0 ] ) {
				args.placeholder = give_stripe_vars.card_number_placeholder_text;
			} else if ( 'cardCvc' === element[ 0 ] ) {
				args.placeholder = give_stripe_vars.card_cvc_placeholder_text;
			} else {
				delete args.placeholder;
			}

			paymentElement.push( stripeElement.create( element[ 0 ], args ) );
		} );

		if ( 'cardNumber' === mountOnElements[ 0 ][ 0 ] ) {
			// Update Card Type for Stripe Multi Fields.
			paymentElement[ 0 ].addEventListener( 'change', function( event ) {
				// Workaround for class name of Diners Club Card.
				const brand = ( 'diners' === event.brand ) ? 'dinersclub' : event.brand;

				// Add Brand to card type wrapper to display specific brand logo based on card number.
				formElement.querySelector( '.card-type' ).className = 'card-type ' + brand;
			} );
		}

		return paymentElement;
	}

	/**
	 * Destroy Card Elements.
	 *
	 * @param elements
	 *
	 * @since 2.8.0
	 *
	 * @returns {[]}
	 */
	destroyElement( elements ) {
		elements.forEach( ( element, index ) => {
			element.destroy();
		} );
	}

	/**
	 * Get Element Styles.
	 *
	 * @since 2.8.0
	 *
	 * @returns {{invalid: *, complete: *, base: *, empty: *}}
	 */
	getElementStyles() {
		const baseStyles = give_stripe_vars.element_base_styles;
		const completeStyles = give_stripe_vars.element_complete_styles;
		const emptyStyles = give_stripe_vars.element_empty_styles;
		const invalidStyles = give_stripe_vars.element_invalid_styles;

		return {
			base: baseStyles,
			complete: completeStyles,
			empty: emptyStyles,
			invalid: invalidStyles,
		};
	}

	/**
	 * Get Element Classes.
	 *
	 * @since 2.8.0
	 *
	 * @returns {{invalid: string, focus: string, empty: string}}
	 */
	getElementClasses() {
		return {
			focus: 'focus',
			empty: 'empty',
			invalid: 'invalid',
		};
	}

	/**
	 * Get Card Elements to Mount on.
	 *
	 * @since 2.8.0
	 *
	 * @returns {[string, string][]}
	 */
	getElementsToMountOn() {
		let elementsToMountOn = {
			cardNumber: `#give-card-number-field-${ this.idPrefix }`,
			cardCvc: `#give-card-cvc-field-${ this.idPrefix }`,
			cardExpiry: `#give-card-expiration-field-${ this.idPrefix }`,
		};

		if ( this.isSingleInputField ) {
			elementsToMountOn = {
				card: `#give-stripe-single-cc-fields-${ this.idPrefix }`,
			};
		}

		return Object.entries( elementsToMountOn );
	}

	/**
	 * Mount Element.
	 *
	 * @param stripeElements
	 *
	 * @since 2.8.0
	 */
	mountElement( stripeElements ) {
		const mountOnElement = this.getElementsToMountOn();

		Array.from( stripeElements ).forEach( ( element, index ) => {
			element.mount( mountOnElement[ index ][ 1 ] );
		} );
	}

	/**
	 * UnMount Element.
	 *
	 * @param stripeElement
	 *
	 * @since 2.8.0
	 */
	unMountElement( stripeElement ) {
		const unMountOnElement = this.getElementsToMountOn();

		Array.from( stripeElement ).forEach( ( element, index ) => {
			element.unmount( unMountOnElement[ index ][ 1 ] );
		} );
	}

	/**
	 * UnMount Element.
	 *
	 * @param formElement
	 * @param stripeElement
	 * @param cardElements
	 *
	 * @since 2.9.4 only add card name for multi-input field
	 * @since 2.8.0
	 */
	createPaymentMethod( formElement, stripeElement, cardElements ) {
		const billing_details = {};

		if ( ! this.isSingleInputField ) {
			billing_details.name = formElement.querySelector( 'input[name="card_name"]' ).value;
		}

		if ( ! give_stripe_vars.stripe_card_update ) {
			const firstName = formElement.querySelector( 'input[name="give_first"]' ).value;
			const lastName = formElement.querySelector( 'input[name="give_last"]' ).value;
			const email = formElement.querySelector( 'input[name="give_email"]' ).value;

			billing_details.name = `${ firstName } ${ lastName }`;
			billing_details.email = email;

			const formSubmit = formElement.querySelector( '[id^=give-purchase-button]' );

			// Disable the submit button to prevent repeated clicks.
			formSubmit.setAttribute( 'disabled', 'disabled' );
		}

		// Gather additional customer data we may have collected in our form.
		if ( give_stripe_vars.checkout_address && ! give_stripe_vars.stripe_card_update ) {
			const address1 = formElement.querySelector( '.card-address' ).value;
			const address2 = formElement.querySelector( '.card-address-2' ).value;
			const city = formElement.querySelector( '.card-city' ).value;
			const state = formElement.querySelector( '.card_state' ).value;
			const zip = formElement.querySelector( '.card-zip' ).value;
			const country = formElement.querySelector( '.billing-country' ).value;

			billing_details.address = {
				line1: address1 ? address1 : '',
				line2: address2 ? address2 : '',
				city: city ? city : '',
				state: state ? state : '',
				postal_code: zip ? zip : '',
				country: country ? country : '',
			};
		}

		// Create Payment Method using the CC fields.
		stripeElement.createPaymentMethod( {
			type: 'card',
			card: cardElements[ 0 ],
			billing_details: billing_details,
		} ).then( function( result ) {
			if ( result.error ) {
				const jQueryFormElement = jQuery( formElement );
				const error = `<div class="give_errors"><p class="give_error">${ result.error.message }</p></div>`;
				const formId = formElement.getAttribute( 'data-id' );

				Give.form.fn.resetDonationButton( jQueryFormElement );
				formElement.querySelector( `#give-stripe-payment-errors-${ formId }` ).innerHTML = error;

				return;
			}

			formElement.querySelector( 'input[name="give_stripe_payment_method"]' ).value = result.paymentMethod.id;
			formElement.submit();
		} );
	}

	/**
	 * Trigger Stripe Checkout Modal.
	 *
	 * @param formElement
	 * @param stripeElements
	 * @param setupStripeElement
	 * @param cardElements
	 *
     * @since 2.32.0 Scrolls Stripe checkout modal into view for all screen sizes.
     *
	 * @since 2.8.0
	 */
	triggerStripeModal( formElement, stripeElements, setupStripeElement, cardElements ) {
        const idPrefixElement = formElement.querySelector('input[name="give-form-id-prefix"]');
        const stripeModalDonateBtn = formElement.querySelector(
            `#give-stripe-checkout-modal-donate-button-${idPrefixElement.value}`
        );
        const cardholderName = formElement.querySelector('input[name="card_name"]');
        const stripeModalContent = document.querySelector('.give-stripe-checkout-modal-container');
        const purchaseButton = document.querySelector('#give-purchase-button');
        const completeCardElements = {};
        let completeCardStatus = false;

        // Scroll checkout modal container into view.
        purchaseButton.addEventListener('click', function () {
            stripeModalContent.scrollIntoView({behavior: 'smooth'});
        });

        cardElements.forEach((cardElement) => {
            completeCardElements.cardName = false;

            cardElement.addEventListener('ready', (e) => {
                completeCardElements[e.elementType] = false;
                completeCardElements.cardName = 'card' === e.elementType;
            });

            cardElement.addEventListener('change', (e) => {
                completeCardElements[e.elementType] = e.complete;
                completeCardStatus = Object.values(completeCardElements).every((string) => {
                    return true === string;
                });

                completeCardStatus
                    ? stripeModalDonateBtn.removeAttribute('disabled')
                    : stripeModalDonateBtn.setAttribute('disabled', 'disabled');
            });
        });

        if (null !== cardholderName) {
            cardholderName.addEventListener('keyup', (e) => {
                completeCardElements.cardName = '' !== e.target.value;
                completeCardStatus = Object.values(completeCardElements).every((string) => {
                    return true === string;
                });
                completeCardStatus
                    ? stripeModalDonateBtn.removeAttribute('disabled')
                    : stripeModalDonateBtn.setAttribute('disabled', 'disabled');
            });
        }

        if (null !== stripeModalDonateBtn) {
            // Process donation on the click of the modal donate button.
            stripeModalDonateBtn.addEventListener('click', (e) => {
                const currentModalDonateBtn = e.target;
                const loadingAnimationElement = currentModalDonateBtn.nextElementSibling;
                const isLegacyForm = stripeModalDonateBtn.getAttribute('data-is_legacy_form');

                if (isLegacyForm) {
                    currentModalDonateBtn.value = give_global_vars.purchase_loading;
                    loadingAnimationElement.style.display = 'inline-block';
                } else {
                    currentModalDonateBtn.value = '';
                    loadingAnimationElement.classList.add('sequoia-loader');
                    loadingAnimationElement.classList.add('spinning');
                    loadingAnimationElement.classList.remove('give-loading-animation');
                }

                // Create Payment Method.
                stripeElements.createPaymentMethod(formElement, setupStripeElement, cardElements);

                e.preventDefault();
            });
        }
    }
}

export { GiveStripeElements };
