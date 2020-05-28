/**
 * Give - Stripe BECS Payment Method JS
 */
const stripe = {};

document.addEventListener( 'DOMContentLoaded', function( e ) {
	// Register Variables.
	let card = {};
	let bankAccountElements = [];
	let defaultGateway = '';
	const globalIbanElements = [];
	let bankAccountElementSelectors = [];
	const fontStyles = [];
	const preferredLocale = give_stripe_vars.preferred_locale;
	const formWraps = document.querySelectorAll( '.give-form-wrap' );

	// If font styles are defined, add them to font styles array
	if ( Object.keys( give_stripe_vars.element_font_styles ).length !== 0 ) {
		fontStyles.push( give_stripe_vars.element_font_styles );
	}

	// Loop through the number of forms on the page.
	Array.prototype.forEach.call( formWraps, function( formWrap ) {
		const formElement = formWrap.querySelector( '.give-form' );

		/**
		 * Bailout, if `formElement` is null.
		 *
		 * We are bailing out here as this script is loaded on every page of the
		 * site but the `formElement` only exists on the pages when Give donation
		 * form is loaded. So, when the pages where the donation form is not loaded
		 * will show console error. To avoid JS console errors we bail it, if
		 * `formElement` is null to avoid console errors.
		 */
		if ( null === formElement ) {
			return;
		}

		const publishableKey = formElement.getAttribute( 'data-publishable-key' );
		const accountId = formElement.getAttribute( 'data-account' );
		const idPrefix = formElement.getAttribute( 'data-id' );

		/**
		 * Bailout, when publishable key is not present for a donation form
		 * due to Stripe account not properly attached to the form or global
		 * Stripe account is not added.
		 */
		if ( null === publishableKey ) {
			return;
		}

		stripe[ idPrefix ] = Stripe( publishableKey );

		if ( accountId.trim().length !== 0 ) {
			stripe[ idPrefix ] = Stripe( publishableKey, {
				stripeAccount: accountId,
			} );
		}

		let elements = stripe[ idPrefix ].elements( {
			locale: preferredLocale,
		} );

		// Update fonts of Stripe Elements.
		if ( fontStyles.length > 0 ) {
			elements = stripe[ idPrefix ].elements( {
				fonts: fontStyles,
				locale: preferredLocale,
			} );
		}

		if ( null !== formElement.querySelector( '.give-gateway:checked' ).value ) {
			defaultGateway = formElement.querySelector( '.give-gateway:checked' ).value;
		}

		const donateButton = formElement.querySelector( '.give-submit' );

		// Create IBAN Elements for each form.
		bankAccountElements = giveStripePrepareIbanElements( formElement, elements, idPrefix );

		bankAccountElementSelectors = [ '#give-stripe-becs-fields-' ];

		// Prepare Card Elements for each form on a single page.
		globalIbanElements[ idPrefix ] = [];

		Array.prototype.forEach.call( bankAccountElementSelectors, function( selector, index ) {
			globalIbanElements[ idPrefix ][ index ] = [];
			globalIbanElements[ idPrefix ][ index ].item = bankAccountElements[ index ];
			globalIbanElements[ idPrefix ][ index ].selector = selector;
			globalIbanElements[ idPrefix ][ index ].isCardMounted = false;
		} );

		// Mount and Un-Mount Stripe CC Fields on gateway load.
		jQuery( document ).on( 'give_gateway_loaded', function( event, xhr, settings ) {
			// Un-mount card elements when stripe is not the selected gateway.
			giveStripeUnmountIbanElements( globalIbanElements[ idPrefix ] );

			if ( formElement.querySelector( '.give-gateway-option-selected .give-gateway' ).value === 'stripe_becs' ) {
				// Mount card elements when stripe is the selected gateway.
				giveStripeMountIbanElements( idPrefix, globalIbanElements[ idPrefix ] );
			}
		} );

		// Mount Card Elements, if default gateway is Stripe BECS.
		if ( 'stripe_becs' === defaultGateway || give_stripe_vars.stripe_becs_update ) {
			// Disabled the donate button of the form.
			donateButton.setAttribute( 'disabled', 'disabled' );

			giveStripeMountIbanElements( idPrefix, globalIbanElements[ idPrefix ] );

			// Enable the donate button of the form after successful mounting of CC fields.
			donateButton.removeAttribute( 'disabled' );
		} else {
			giveStripeUnmountIbanElements( bankAccountElements );
		}
	} );

	// Process Donation using Stripe Elements on form submission.
	jQuery( 'body' ).on( 'submit', '.give-form', function( event ) {
		const $form = jQuery( this );
		const $idPrefix = $form.find( 'input[name="give-form-id-prefix"]' ).val();

		if ( 'stripe_becs' === $form.find( 'input.give-gateway:checked' ).val() || give_stripe_vars.stripe_becs_update ) {
			give_stripe_process_becs_bank_account( $form, globalIbanElements[ $idPrefix ][ 0 ].item );
			event.preventDefault();
		}
	} );

	/**
	 * Mount IBAN Elements
	 *
	 * @param {string} idPrefix     ID Prefix.
	 * @param {array}  bankAccountElements List of card elements to be mounted.
	 *
	 * @since 2.6.3
	 */
	function giveStripeMountIbanElements( idPrefix, bankAccountElements = [] ) {
		const bankAccountElementsLength = Object.keys( bankAccountElements ).length;

		// Assign any card element to variable to create source.
		if ( bankAccountElementsLength > 0 ) {
			card = bankAccountElements[ 0 ].item;
		}

		// Mount required card elements.
		Array.prototype.forEach.call( bankAccountElements, function( value, index ) {
			if ( false === value.isCardMounted ) {
				value.item.mount( value.selector + idPrefix );
				value.isCardMounted = true;
			}
		} );
	}

	/**
	 * Un-mount IBAN Elements
	 *
	 * @param {array} bankAccountElements List of card elements to be unmounted.
	 *
	 * @since 2.6.3
	 */
	function giveStripeUnmountIbanElements( bankAccountElements = [] ) {
		// Un-mount required card elements.
		Array.prototype.forEach.call( bankAccountElements, function( value, index ) {
			if ( true === value.isCardMounted ) {
				value.item.unmount();
				value.isCardMounted = false;
			}
		} );
	}

	/**
	 * Create required IBAN elements.
	 *
	 * @param {object} formElement Form Element.
	 * @param {object} elements     Stripe Element.
	 * @param {string} idPrefix     ID Prefix.
	 *
	 * @since 2.6.3
	 *
	 * @return {array} elements
	 */
	function giveStripePrepareIbanElements( formElement, elements, idPrefix ) {
		const prepareCardElements = [];
		const baseStyles = give_stripe_vars.element_base_styles;
		const completeStyles = give_stripe_vars.element_complete_styles;
		const emptyStyles = give_stripe_vars.element_empty_styles;
		const invalidStyles = give_stripe_vars.element_invalid_styles;
		const becsElement = formElement.querySelector( '#give-stripe-becs-fields-' + idPrefix );

		const elementStyles = {
			base: baseStyles,
			complete: completeStyles,
			empty: emptyStyles,
			invalid: invalidStyles,
		};

		const elementClasses = {
			focus: 'focus',
			empty: 'empty',
			invalid: 'invalid',
		};

		const bankAccountCreateArgs = {
			style: elementStyles,
			classes: elementClasses,
		};

		if ( 'stripe_becs' === defaultGateway ) {
			const hideIcon = becsElement.getAttribute( 'data-hide_icon' );
			const iconStyle = becsElement.getAttribute( 'data-icon_style' );

			bankAccountCreateArgs.iconStyle = iconStyle;
			bankAccountCreateArgs.hideIcon = ( 'disabled' === hideIcon );
		}

		const bankAccountElement = elements.create(
			'auBankAccount',
			bankAccountCreateArgs
		);

		prepareCardElements.push( bankAccountElement );

		return prepareCardElements;
	}

	/**
	 * Stripe Response Handler
	 *
	 * @see https://stripe.com/docs/tutorials/forms
	 *
	 * @param {object} $form    Form Object.
	 * @param {object} response Response Object containing source.
	 */
	function give_stripe_response_handler( $form, response ) {
		// Add Source to hidden field for form submission.
		$form.find( 'input[name="give_stripe_payment_method"]' ).val( response.id );

		// Submit the form.
		$form.get( 0 ).submit();
	}

	/**
	 * Stripe Process BECS payment.
	 *
	 * @param {object} $form Form Object.
	 * @param {object} $iban IBAN Object.
	 *
	 * @returns {boolean} True or False.
	 */
	function give_stripe_process_becs_bank_account( $form, $iban ) {
		const additionalData = {
			billing_details: {
				name: '',
				email: '',
			},
		};
		const $form_id = $form.find( 'input[name="give-form-id"]' ).val();
		const idPrefix = $form.find( 'input[name="give-form-id-prefix"]' ).val();
		const $firstName = $form.find( 'input[name="give_first"]' ).val();
		const $lastName = $form.find( 'input[name="give_last"]' ).val();
		const $email = $form.find( 'input[name="give_email"]' ).val();
		const $form_submit_btn = $form.find( '[id^=give-purchase-button]' );

		// Disable the submit button to prevent repeated clicks.
		$form.find( '[id^=give-purchase-button]' ).attr( 'disabled', 'disabled' );

		additionalData.billing_details.name = $firstName + ' ' + $lastName;
		additionalData.billing_details.email = $email;

		// Gather additional customer data we may have collected in our form.
		if ( give_stripe_vars.checkout_address && ! give_stripe_vars.stripe_becs_update ) {
			const address1 = $form.find( '.card-address' ).val();
			const address2 = $form.find( '.card-address-2' ).val();
			const city = $form.find( '.card-city' ).val();
			const state = $form.find( '.card_state' ).val();
			const zip = $form.find( '.card-zip' ).val();
			const country = $form.find( '.billing-country' ).val();

			additionalData.billing_details.address = {
				line1: address1 ? address1 : '',
				line2: address2 ? address2 : '',
				city: city ? city : '',
				state: state ? state : '',
				postal_code: zip ? zip : '',
				country: country ? country : '',
			};
		}

		// createPaymentMethod returns immediately - the supplied callback submits the form if there are no errors.
		stripe[ idPrefix ].createPaymentMethod( 'au_becs_debit', $iban, additionalData ).then( function( result ) {
			if ( result.error ) {
				const error = `<div class="give_errors"><p class="give_error">${ result.error.message }</p></div>`;

				// re-enable the submit button.
				$form_submit_btn.attr( 'disabled', false );

				// Hide the loading animation.
				jQuery( '.give-loading-animation' ).fadeOut();

				// Display Error on the form.
				$form.find( '[id^=give-stripe-payment-errors-' + $form_id + ']' ).html( error );

				// Reset Donate Button.
				if ( give_global_vars.complete_purchase ) {
					$form_submit_btn.val( give_global_vars.complete_purchase );
				} else {
					$form_submit_btn.val( $form_submit_btn.data( 'before-validation-label' ) );
				}
			} else {
				// Send payment method to server for processing payment.
				give_stripe_response_handler( $form, result.paymentMethod );
			}
		} );

		return false; // Submit from callback.
	}
} );
