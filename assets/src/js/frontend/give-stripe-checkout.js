/**
 * Give - Stripe Popup Checkout JS
 */

document.addEventListener( 'DOMContentLoaded', ( evt ) => {
	const stripe_handler = [];
	const formWraps = document.querySelectorAll( '.give-form-wrap' );

	// Loop through the number of forms on the page.
	Array.prototype.forEach.call( formWraps, function( formWrap ) {
		let token_created = false;
		const form_element = formWrap.querySelector( '.give-form' );
		const formName = form_element.querySelector( 'input[name="give-form-title"]' ).value;
		const idPrefix = form_element.querySelector( 'input[name="give-form-id-prefix"]' ).value;
		const checkoutImage = ( give_stripe_vars.checkout_image.length > 0 ) ? give_stripe_vars.checkout_image : '';
		const checkoutAddress = ( give_stripe_vars.checkout_address.length > 0 );
		const isZipCode = ( give_stripe_vars.zipcode_option.length > 0 );
		const isRememberMe = ( give_stripe_vars.remember_option.length > 0 );

		stripe_handler[ idPrefix ] = [];

		// Set stripe handler for form.
		stripe_handler[ idPrefix ] = StripeCheckout.configure( {
			key: give_stripe_vars.publishable_key,
			image: checkoutImage,
			locale: give_stripe_vars.preferred_locale,
			billingAddress: checkoutAddress,
			token: function( token, args ) {
				const processingHtml = document.createElement( 'div' );
				processingHtml.setAttribute( 'class', 'stripe-checkout-process' );
				processingHtml.style.background = '#FFFFFF';
				processingHtml.style.opacity = '0.75';
				processingHtml.style.position = 'fixed';
				processingHtml.style.top = '0';
				processingHtml.style.left = '0';
				processingHtml.style.bottom = '0';
				processingHtml.style.right = '0';
				processingHtml.style.zIndex = '2147483646';
				processingHtml.style.display = 'none';
				processingHtml.innerHTML = '<div class="give-stripe-checkout-processing-container" style="position: absolute;top: 50%;left: 50%;width: 300px; margin-left: -150px; text-align:center;"><div style="display:inline-block;"><span class="give-loading-animation" style="color: #333;height:26px;width:26px;font-size:26px; margin:0; "></span><span style="color:#333; font-size: 18px; margin:0 0 0 10px;">' + give_stripe_vars.checkout_processing_text + '</span></div></div>';

				token_created = true;

				// For Stripe Connect API Users.
				if ( '' !== give_stripe_vars.stripe_account_id ) {
					token.stripeAccount = give_stripe_vars.stripe_account_id;
				}

				// Supplemental loading animation while the donation form submits.
				// @see: https://github.com/WordImpress/Give-Stripe/issues/79
				form_element.insertAdjacentElement( 'afterend', processingHtml );

				// Assign token to its hidden field for submitting it to the server.
				form_element.querySelector( '#give-stripe-source-' + idPrefix ).value = token.id;

				// // Add billing address fields?
				if ( checkoutAddress ) {
					// Create Billing Country Element.
					const billing_country = document.createElement( 'input' );
					billing_country.setAttribute( 'type', 'hidden' );
					billing_country.setAttribute( 'name', 'billing_country' );
					billing_country.setAttribute( 'value', args.billing_address_country_code );
					form_element.insertAdjacentElement( 'afterbegin', billing_country );

					// Create Billing Address Element.
					const card_address = document.createElement( 'input' );
					card_address.setAttribute( 'type', 'hidden' );
					card_address.setAttribute( 'name', 'card_address' );
					card_address.setAttribute( 'value', args.billing_address_line1 );
					form_element.insertAdjacentElement( 'afterbegin', card_address );

					// Create Billing City Element.
					const card_city = document.createElement( 'input' );
					card_city.setAttribute( 'type', 'hidden' );
					card_city.setAttribute( 'name', 'card_city' );
					card_city.setAttribute( 'value', args.billing_address_city );
					form_element.insertAdjacentElement( 'afterbegin', card_city );

					// Create Billing State Element.
					const card_state = document.createElement( 'input' );
					card_state.setAttribute( 'type', 'hidden' );
					card_state.setAttribute( 'name', 'card_state' );
					card_state.setAttribute( 'value', args.billing_address_state );
					form_element.insertAdjacentElement( 'afterbegin', card_state );

					// Create Billing ZIP Element.
					const card_zip = document.createElement( 'input' );
					card_zip.setAttribute( 'type', 'hidden' );
					card_zip.setAttribute( 'name', 'card_zip' );
					card_zip.setAttribute( 'value', args.billing_address_zip );
					form_element.insertAdjacentElement( 'afterbegin', card_zip );
				}

				const verifyPayment = new XMLHttpRequest();
				const formData = new FormData( form_element );
				const errorElement = form_element.querySelector( '#give-stripe-payment-errors-' + idPrefix );
				let isFormSubmit = false;

				formData.append( 'action', 'give_process_donation' );
				formData.append( 'give_ajax', true );

				// Do something on Ajax on state change.
				verifyPayment.onreadystatechange = function( evt ) {
					if (
						4 === this.readyState &&
						200 === this.status &&
						'success' !== this.responseText
					) {
						evt.preventDefault();

						isFormSubmit = false;

						// Remove added elements when error is found.
						form_element.querySelector( 'input[name="billing_country"]' ).remove();
						form_element.querySelector( 'input[name="card_address"]' ).remove();
						form_element.querySelector( 'input[name="card_state"]' ).remove();
						form_element.querySelector( 'input[name="card_city"]' ).remove();
						form_element.querySelector( 'input[name="card_zip"]' ).remove();

						// Don't show donation processing overlay.
						document.querySelector( '.stripe-checkout-process' ).style.display = 'none';

						// Show Error.
						errorElement.innerHTML = this.response;

						// Refresh Donate Button.
						give_stripe_refresh_donate_button( form_element );
					} else {
						errorElement.innerHTML = '';
						document.querySelector( '.stripe-checkout-process' ).style.display = 'block';
						isFormSubmit = true;
					}
				};
				verifyPayment.open( 'POST', give_global_vars.ajaxurl, false );
				verifyPayment.send( formData );

				if ( true === isFormSubmit ) {
					// Submit form after charge source brought back from Stripe.
					form_element.submit();
				}
			},
			closed: function() {
				// Close button click behavior goes here.
				if ( ! token_created ) {
					give_stripe_refresh_donate_button( form_element );

					// Close handler if it is still open.
					stripe_handler[ idPrefix ].close();
				}
			},
		} );

		form_element.onsubmit = function( evt ) {
			const selectedGateway = form_element.querySelector( '.give-gateway:checked' ).value;

			// If Stripe Checkout is enabled, then restrict default form submission.
			if ( 'stripe' === selectedGateway ) {
				evt.preventDefault();

				const donationAmount = form_element.querySelector( '.give-final-total-amount' ).getAttribute( 'data-total' );
				const donorEmail = form_element.querySelector( 'input[name="give_email"]' ).value;

				// Open Stripe Checkout Modal.
				stripe_handler[ idPrefix ].open( {
					name: give_stripe_vars.sitename,
					description: formName,
					amount: give_stripe_format_currency( donationAmount, form_element ),
					zipCode: isZipCode,
					allowRememberMe: isRememberMe,
					email: donorEmail,
					currency: form_element.getAttribute( 'data-currency_code' ),
				} );
			}
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
