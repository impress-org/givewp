/**
 * Give - Stripe Gateway Add-on JS
 */

let stripe = Stripe( give_stripe_vars.publishable_key, {
	betas: [ 'payment_intent_beta_3' ],
} );

if ( give_stripe_vars.stripe_account_id ) {
	stripe = Stripe( give_stripe_vars.publishable_key, {
		stripeAccount: give_stripe_vars.stripe_account_id,
		betas: [ 'payment_intent_beta_3' ],
	} );
}

document.addEventListener( 'DOMContentLoaded', function( e ) {
	// Register Variables.
	let card = {};
	let cardElements = [];
	let defaultGateway = '';
	const globalCardElements = [];
	let cardElementSelectors = [];
	const fontStyles = [];
	const preferredLocale = give_stripe_vars.preferred_locale;
	const formWraps = document.querySelectorAll( '.give-form-wrap' );
	const fontIterator = Object.entries( give_stripe_vars.element_font_styles );

	// Loop through each font element to convert its object to array.
	for ( const fontElement of fontIterator ) {
		fontStyles[ fontElement[ 0 ] ] = fontElement[ 1 ];
	}

	// Loop through the number of forms on the page.
	Array.prototype.forEach.call( formWraps, function( formWrap ) {
		const form_element = formWrap.querySelector( '.give-form' );

		let elements = stripe.elements( {
			locale: preferredLocale,
		} );

		// Update fonts of Stripe Elements.
		if ( fontStyles.length > 0 ) {
			elements = stripe.elements( {
				fonts: fontStyles,
				locale: preferredLocale,
			} );
		}

		if ( null !== form_element.querySelector( '.give-gateway:checked' ).value ) {
			defaultGateway = form_element.querySelector( '.give-gateway:checked' ).value;
		}

		const idPrefix = form_element.getAttribute( 'data-id' );
		const donateButton = form_element.querySelector( '.give-submit' );

		// Create Card Elements for each form.
		cardElements = giveStripePrepareCardElements( form_element, elements, idPrefix );

		if ( 'single' === give_stripe_vars.cc_fields_format ) {
			cardElementSelectors = [ '#give-stripe-single-cc-fields-' ];
		} else if ( 'multi' === give_stripe_vars.cc_fields_format ) {
			cardElementSelectors = [ '#give-card-number-field-', '#give-card-cvc-field-', '#give-card-expiration-field-' ];
		}

		// Prepare Card Elements for each form on a single page.
		globalCardElements[ idPrefix ] = [];

		Array.prototype.forEach.call( cardElementSelectors, function( selector, index ) {
			globalCardElements[ idPrefix ][ index ] = [];
			globalCardElements[ idPrefix ][ index ].item = cardElements[ index ];
			globalCardElements[ idPrefix ][ index ].selector = selector;
			globalCardElements[ idPrefix ][ index ].isCardMounted = false;
		} );

		// Mount and Un-Mount Stripe CC Fields on gateway load.
		jQuery( document ).on( 'give_gateway_loaded', function( event, xhr, settings ) {
			// Un-mount card elements when stripe is not the selected gateway.
			giveStripeUnmountCardElements( globalCardElements[ idPrefix ] );

			if ( form_element.querySelector( '.give-gateway-option-selected .give-gateway' ).value === 'stripe' ) {
				// Disable the donate button of the form.
				donateButton.setAttribute( 'disabled', 'disabled' );

				// Mount card elements when stripe is the selected gateway.
				giveStripeMountCardElements( idPrefix, globalCardElements[ idPrefix ] );

				// Enable the donate button of the form after successful mounting of CC fields.
				donateButton.removeAttribute( 'disabled' );
			}

			// Convert normal fields to float labels.
			giveStripeTriggerFloatLabels( idPrefix, form_element );
		} );

		// Mount Card Elements, if default gateway is stripe.
		if ( 'stripe' === defaultGateway ) {
			// Disabled the donate button of the form.
			donateButton.setAttribute( 'disabled', 'disabled' );

			giveStripeMountCardElements( idPrefix, globalCardElements[ idPrefix ] );

			// Enable the donate button of the form after successful mounting of CC fields.
			donateButton.removeAttribute( 'disabled' );
		} else {
			giveStripeUnmountCardElements( cardElements );
		}

		// Convert normal fields to float labels.
		giveStripeTriggerFloatLabels( idPrefix, form_element );
	} );

	// Process Donation using Stripe Elements on form submission.
	jQuery( 'body' ).on( 'submit', '.give-form', function( event ) {
		const $form = jQuery( this );
		const $idPrefix = $form.find( 'input[name="give-form-id-prefix"]' ).val();

		if ( 'stripe' === $form.find( 'input.give-gateway:checked' ).val() || give_stripe_vars.stripe_card_update ) {
			give_stripe_process_card( $form, globalCardElements[ $idPrefix ][ 0 ].item );
			event.preventDefault();
		}
	} );

	/**
	 * Trigger Float Labels when enabled.
	 *
	 * @param {string} idPrefix ID Prefix.
	 * @param {object} form     Form Object.
	 *
	 * @since 2.0
	 */
	function giveStripeTriggerFloatLabels( idPrefix, form ) {
		// Process it when float labels is enabled.
		if ( form.classList.contains( 'float-labels-enabled' ) ) {
			Array.prototype.forEach.call( form.querySelectorAll( '.give-stripe-cc-field-wrap' ), function( element, index ) {
				const ccLabelSelector = element.querySelector( 'label' );
				const ccInnerDivSelector = element.querySelector( 'div' );
				const ccInputSelector = element.querySelector( '.give-stripe-cc-field' );
				const ccWrapSelector = ccLabelSelector.parentElement;

				if ( ! Array.prototype.includes( 'give-fl-label', ccLabelSelector.classList ) ) {
					ccLabelSelector.className = ccLabelSelector.classList + ' give-fl-label';
				}

				if ( ! Array.prototype.includes( 'give-fl-label', ccLabelSelector.classList ) ) {
					ccInputSelector.className = ccInputSelector.classList + ' give-fl-input';
				}

				if ( ! Array.prototype.includes( 'give-fl-wrap give-fl-wrap-input give-fl-is-required', ccInnerDivSelector.classList ) ) {
					ccInnerDivSelector.className = ccInnerDivSelector.classList + ' give-fl-wrap give-fl-wrap-input give-fl-is-required';
				}

				Array.prototype.forEach.call( globalCardElements[ idPrefix ], function( globalElement ) {
					if ( globalElement.selector.indexOf( ccInputSelector.id ) > 0 ) {
						globalElement.item.on( 'change', function( e ) {
							if (
								( e.empty === false || e.complete === true ) &&
								! Array.prototype.includes( 'give-fl-is-active', ccWrapSelector.classList )
							) {
								ccWrapSelector.className = ccWrapSelector.classList + ' give-fl-is-active';
							} else if ( e.empty === true && e.complete === false ) {
								ccWrapSelector.classList.remove( 'give-fl-is-active' );
								ccWrapSelector.className = ccWrapSelector.classList;
							}
						} );
					}
				} );
			} );
		}
	}

	/**
	 * Mount Card Elements
	 *
	 * @param {string} idPrefix     ID Prefix.
	 * @param {array}  cardElements List of card elements to be mounted.
	 *
	 * @since 1.6
	 */
	function giveStripeMountCardElements( idPrefix, cardElements = [] ) {
		const cardElementsLength = Object.keys( cardElements ).length;

		// Assign any card element to variable to create source.
		if ( cardElementsLength > 0 ) {
			card = cardElements[ 0 ].item;
		}

		// Mount required card elements.
		Array.prototype.forEach.call( cardElements, function( value, index ) {
			if ( false === value.isCardMounted ) {
				value.item.mount( value.selector + idPrefix );
				value.isCardMounted = true;
			}
		} );
	}

	/**
	 * Un-mount Card Elements
	 *
	 * @param {array} cardElements List of card elements to be unmounted.
	 *
	 * @since 1.6
	 */
	function giveStripeUnmountCardElements( cardElements = [] ) {
		// Un-mount required card elements.
		Array.prototype.forEach.call( cardElements, function( value, index ) {
			if ( true === value.isCardMounted ) {
				value.item.unmount();
				value.isCardMounted = false;
			}
		} );
	}

	/**
	 * Create required card elements.
	 *
	 * @param {object} elements     Stripe Element.
	 * @param {object} form_element Form Element.
	 * @param {string} idPrefix     ID Prefix.
	 *
	 * @since 1.6
	 *
	 * @returns {array}
	 */
	function giveStripePrepareCardElements( form_element, elements, idPrefix ) {
		const prepareCardElements = [];
		const baseStyles = give_stripe_vars.element_base_styles;
		const completeStyles = give_stripe_vars.element_complete_styles;
		const emptyStyles = give_stripe_vars.element_empty_styles;
		const invalidStyles = give_stripe_vars.element_invalid_styles;

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

		// Mount CC Fields based on the settings.
		if ( 'multi' === give_stripe_vars.cc_fields_format ) {
			const cardNumber = elements.create(
				'cardNumber',
				{
					style: elementStyles,
					classes: elementClasses,
					placeholder: give_stripe_vars.card_number_placeholder_text,
				}
			);

			// Update Card Type for Stripe Multi Fields.
			cardNumber.addEventListener( 'change', function( event ) {
				// Workaround for class name of Diners Club Card.
				const brand = ( 'diners' === event.brand ) ? 'dinersclub' : event.brand;

				// Add Brand to card type wrapper to display specific brand logo based on card number.
				form_element.querySelector( '.card-type' ).className = 'card-type ' + brand;
			} );

			const cardExpiry = elements.create(
				'cardExpiry',
				{
					style: elementStyles,
					classes: elementClasses,
				}
			);

			const cardCvc = elements.create(
				'cardCvc',
				{
					style: elementStyles,
					classes: elementClasses,
					placeholder: give_stripe_vars.card_cvc_placeholder_text,
				}
			);

			prepareCardElements.push( cardNumber, cardCvc, cardExpiry );
		} else if ( 'single' === give_stripe_vars.cc_fields_format ) {
			const card = elements.create(
				'card',
				{
					style: elementStyles,
					classes: elementClasses,
					hidePostalCode: !! ( give_stripe_vars.checkout_address ),
				}
			);

			prepareCardElements.push( card );
		}

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
		$form.find( 'input[name="give_stripe_source"]' ).val( response.id );

		// Submit the form.
		$form.get( 0 ).submit();
	}

	/**
	 * Stripe Process CC
	 *
	 * @param {object} $form Form Object.
	 * @param {object} card  Card Object.
	 *
	 * @returns {boolean}
	 */
	function give_stripe_process_card( $form, card ) {
		const additionalData = {
			type: 'card',
			owner: {},
		};
		const $form_id = $form.find( 'input[name="give-form-id"]' ).val();
		const $form_submit_btn = $form.find( '[id^=give-purchase-button]' );
		const card_name = $form.find( '.card-name' ).val();

		// disable the submit button to prevent repeated clicks.
		$form.find( '[id^=give-purchase-button]' ).attr( 'disabled', 'disabled' );

		// Set Card Name to Source.
		if ( 'multi' === give_stripe_vars.cc_fields_format && '' !== card_name ) {
			additionalData.owner.name = card_name;
		}

		// Gather additional customer data we may have collected in our form.
		if ( give_stripe_vars.checkout_address ) {
			const address1 = $form.find( '.card-address' ).val();
			const address2 = $form.find( '.card-address-2' ).val();
			const city = $form.find( '.card-city' ).val();
			const state = $form.find( '.card_state' ).val();
			const zip = $form.find( '.card-zip' ).val();
			const country = $form.find( '.billing-country' ).val();

			additionalData.owner.address = {
				line1: address1 ? address1 : '',
				line2: address2 ? address2 : '',
				city: city ? city : '',
				state: state ? state : '',
				postal_code: zip ? zip : '',
				country: country ? country : '',
			};
		}

		const clientSecret = $form.find( 'input[name="give_stripe_intent_client_secret"]' ).val();
		stripe.handleCardPayment( clientSecret, card ).then( function( result ) {
			console.log( result );
			e.preventDefault();
			if ( result.error ) {
			  // Display error.message in your UI.
			} else {
			  // The payment has succeeded. Display a success message.
			}
		} );

		// createSource returns immediately - the supplied callback submits the form if there are no errors.
		// stripe.createSource( card, additionalData ).then( function( result ) {
		// 	if ( result.error ) {
		// 		const error = '<div class="give_errors"><p class="give_error">' + result.error.message + '</p></div>';

		// 		// re-enable the submit button.
		// 		$form_submit_btn.attr( 'disabled', false );

		// 		// Hide the loading animation.
		// 		jQuery( '.give-loading-animation' ).fadeOut();

		// 		// Display Error on the form.
		// 		$form.find( '[id^=give-stripe-payment-errors-' + $form_id + ']' ).html( error );

		// 		// Reset Donate Button.
		// 		if ( give_global_vars.complete_purchase ) {
		// 			$form_submit_btn.val( give_global_vars.complete_purchase );
		// 		} else {
		// 			$form_submit_btn.val( $form_submit_btn.data( 'before-validation-label' ) );
		// 		}
		// 	} else {
		// 		// Send source to server for processing payment.
		// 		give_stripe_response_handler( $form, result.source );
		// 	}
		// } );

		return false; // Submit from callback.
	}
} );
