/**
 * Give - Stripe Core Admin JS
 *
 * @since 2.5.0
 */

import { GiveConfirmModal } from '../plugins/modal';

window.addEventListener( 'DOMContentLoaded', function() {
	const ccFormatSettings = document.querySelector( '.stripe-cc-field-format-settings' );
	const stripeFonts = document.querySelectorAll( 'input[name="stripe_fonts"]' );
	const stripeStylesBase = document.getElementById( 'stripe_styles_base' );
	const stripeStylesEmpty = document.getElementById( 'stripe_styles_empty' );
	const stripeStylesInvalid = document.getElementById( 'stripe_styles_invalid' );
	const stripeStylesComplete = document.getElementById( 'stripe_styles_complete' );
	const stripeCustomFonts = document.getElementById( 'stripe_custom_fonts' );
	const donationStatus = document.getElementById( 'give-payment-status' );
	const stripeDisconnect = document.querySelector( '.give-stripe-disconnect' );
	const checkoutTypes = document.querySelectorAll( 'input[name="stripe_checkout_type"]' );
	const legacyCheckoutFields = Array.from( document.querySelectorAll( '.stripe-checkout-field' ) );
	const stripeConnectedElement = document.getElementById( 'give-stripe-connect' );
	const hideIconElements = Array.from( document.querySelectorAll( 'input[name="stripe_hide_icon"]' ) );
	const iconStyleElement = document.querySelector( '.stripe-icon-style' );
	const hideMandateElements = Array.from( document.querySelectorAll( ' input[name="stripe_mandate_acceptance_option"]' ) );
	const mandateElement = document.querySelector( '.stripe-mandate-acceptance-text' );
	const hideBecsIconElements = Array.from( document.querySelectorAll( 'input[name="stripe_becs_hide_icon"]' ) );
	const becsIconStyleElement = document.querySelector( '.stripe-becs-icon-style' );
	const hideBecsMandateElements = Array.from( document.querySelectorAll( ' input[name="stripe_becs_mandate_acceptance_option"]' ) );
	const mandateBecsElement = document.querySelector( '.stripe-becs-mandate-acceptance-text' );

	giveStripeJsonFormattedTextarea( stripeStylesBase );
	giveStripeJsonFormattedTextarea( stripeStylesEmpty );
	giveStripeJsonFormattedTextarea( stripeStylesInvalid );
	giveStripeJsonFormattedTextarea( stripeStylesComplete );
	giveStripeJsonFormattedTextarea( stripeCustomFonts );

	if ( null !== hideIconElements ) {
		hideIconElements.forEach( ( hideIconElement ) => {
			hideIconElement.addEventListener( 'change', ( e ) => {
				if ( 'enabled' === e.target.value ) {
					iconStyleElement.classList.remove( 'give-hidden' );
				} else {
					iconStyleElement.classList.add( 'give-hidden' );
				}
			} );
		} );
	}
	if ( null !== hideMandateElements ) {
		hideMandateElements.forEach( ( hideIconElement ) => {
			hideIconElement.addEventListener( 'change', ( e ) => {
				if ( 'enabled' === e.target.value ) {
					mandateElement.classList.remove( 'give-hidden' );
				} else {
					mandateElement.classList.add( 'give-hidden' );
				}
			} );
		} );
	}

	// For BECS Direct Debit.
	if ( null !== hideBecsIconElements ) {
		hideBecsIconElements.forEach( ( hideIconElement ) => {
			hideIconElement.addEventListener( 'change', ( e ) => {
				if ( 'enabled' === e.target.value ) {
					becsIconStyleElement.classList.remove( 'give-hidden' );
				} else {
					becsIconStyleElement.classList.add( 'give-hidden' );
				}
			} );
		} );
	}
	if ( null !== hideBecsMandateElements ) {
		hideBecsMandateElements.forEach( ( hideIconElement ) => {
			hideIconElement.addEventListener( 'change', ( e ) => {
				if ( 'enabled' === e.target.value ) {
					mandateBecsElement.classList.remove( 'give-hidden' );
				} else {
					mandateBecsElement.classList.add( 'give-hidden' );
				}
			} );
		} );
	}

	if ( null !== stripeConnectedElement ) {
		const stripeStatus = stripeConnectedElement.getAttribute( 'data-status' );
		const redirectUrl = stripeConnectedElement.getAttribute( 'data-redirect-url' );
		const canDisplay = stripeConnectedElement.getAttribute( 'data-display' );
		const modalTitle = stripeConnectedElement.getAttribute( 'data-title' );
		const modalFirstDetail = stripeConnectedElement.getAttribute( 'data-first-detail' );
		const modalSecondDetail = stripeConnectedElement.getAttribute( 'data-second-detail' );

		if ( 'connected' === stripeStatus && '0' === canDisplay ) {
			new GiveConfirmModal(
				{
					modalWrapper: 'give-stripe-connected-modal give-modal--success',
					type: 'confirm',
					modalContent: {
						title: modalTitle,
						desc: `<span>${ modalFirstDetail }</span><span class="give-field-description">${ modalSecondDetail }</span>`,
					},
					successConfirm: function( args ) {
						window.location.href = redirectUrl;
					},
				}
			).render();

			stripeConnectedElement.setAttribute( 'data-display', '1' );
			history.pushState( { urlPath: redirectUrl }, '', redirectUrl );
		}
	}

	if ( null !== checkoutTypes ) {
		checkoutTypes.forEach( ( checkoutType ) => {
			checkoutType.addEventListener( 'change', ( e ) => {
				if ( 'modal' === e.target.value ) {
					legacyCheckoutFields.map( field => field.classList.remove( 'give-hidden' ) );
				} else {
					legacyCheckoutFields.map( field => field.classList.add( 'give-hidden' ) );
				}
			} );
		} );
	}

	if ( null !== stripeDisconnect ) {
		document.querySelector( '.give-stripe-disconnect' ).addEventListener( 'click', ( e ) => {
			e.preventDefault();

			new Give.modal.GiveConfirmModal( {
				type: 'alert',
				classes: {
					modalWrapper: 'give-modal--warning',
				},
				modalContent: {
					title: Give.fn.getGlobalVar( 'disconnect_stripe_title' ),
					desc: Give.fn.getGlobalVar( 'disconnect_stripe_message' ),
				},
				successConfirm: () => {
					window.location.href = e.target.getAttribute( 'href' );
				},
			} ).render();
		} );
	}

	if ( null !== donationStatus ) {
		donationStatus.addEventListener( 'change', ( event ) => {
			const stripeCheckbox = document.getElementById( 'give-stripe-opt-refund' );

			if ( null === stripeCheckbox ) {
				return;
			}

			stripeCheckbox.checked = false;

			// If donation status is complete, then show refund checkbox
			if ( 'refunded' === event.target.value ) {
				document.getElementById( 'give-stripe-opt-refund-wrap' ).style.display = 'block';
			} else {
				document.getElementById( 'give-stripe-opt-refund-wrap' ).style.display = 'none';
			}
		} );
	}

	// Toggle based on selection of stripe fonts admin settings.
	if ( null !== stripeFonts ) {
		stripeFonts.forEach( ( element ) => {
			const stripeGoogleFontsWrap = document.querySelector( '.give-stripe-google-fonts-wrap' );
			const stripeCustomFontsWrap = document.querySelector( '.give-stripe-custom-fonts-wrap' );

			element.addEventListener( 'change', ( event ) => {
				if ( 'custom_fonts' === event.target.value ) {
					stripeGoogleFontsWrap.style.display = 'none';
					stripeCustomFontsWrap.style.display = 'table-row';
				} else if ( 'google_fonts' === event.target.value ) {
					stripeGoogleFontsWrap.style.display = 'table-row';
					stripeCustomFontsWrap.style.display = 'none';
				}
			} );
		} );
	}
} );

/**
 * This function will help to beautify JSON data.
 *
 * @param element
 * @param value
 *
 * @since 2.5.0
 */
function giveStripePrettyJson( element, value ) {
	let jsonData = '';
	const saveButton = document.querySelector( '.give-save-button' );

	try {
		jsonData = JSON.parse( value );
		element.value = JSON.stringify( jsonData, undefined, 2 );
		element.style.border = 'none';
		saveButton.removeAttribute( 'disabled' );
	} catch ( e ) {
		element.style.border = '1px solid red';
		saveButton.setAttribute( 'disabled', 'disabled' );
	}
}

/**
 * This will trigger textarea to validate json formatted input.
 *
 * @param element
 *
 * @since 2.5.0
 */
function giveStripeJsonFormattedTextarea( element ) {
	if ( null !== element ) {
		giveStripePrettyJson( element, element.value );

		element.addEventListener( 'blur', ( event ) => {
			giveStripePrettyJson( element, event.target.value );
		} );
	}
}
