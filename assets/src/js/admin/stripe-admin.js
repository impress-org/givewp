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
	const stripeConnectedElement = document.getElementById( 'give-stripe-connected' );
	const hideIconElements = Array.from( document.querySelectorAll( 'input[name="stripe_hide_icon"]' ) );
	const iconStyleElement = document.querySelector( '.stripe-icon-style' );
	const hideMandateElements = Array.from( document.querySelectorAll( ' input[name="stripe_mandate_acceptance_option"]' ) );
	const mandateElement = document.querySelector( '.stripe-mandate-acceptance-text' );
	const hideBecsIconElements = Array.from( document.querySelectorAll( 'input[name="stripe_becs_hide_icon"]' ) );
	const becsIconStyleElement = document.querySelector( '.stripe-becs-icon-style' );
	const hideBecsMandateElements = Array.from( document.querySelectorAll( ' input[name="stripe_becs_mandate_acceptance_option"]' ) );
	const mandateBecsElement = document.querySelector( '.stripe-becs-mandate-acceptance-text' );
	const manualFields = Array.from( document.querySelectorAll( '.give-stripe-account-type-manual' ) );
	const connectField = document.querySelector( '.give-stripe-account-type-connect' );
	const connectionTypes = Array.from( document.querySelectorAll( 'input[name="stripe_connection_type"]' ) );
	const selectedConnectionType = document.querySelector( 'input[name="stripe_connection_type"]:checked' );
	const disconnectBtns = Array.from( document.querySelectorAll( '.give-stripe-disconnect-account-btn' ) );
	const setStripeDefaults = Array.from( document.querySelectorAll( '.give-stripe-account-set-default' ) );
	const perFormOptions = Array.from( document.querySelectorAll( 'input[name="give_stripe_per_form_accounts"]' ) );
	const perFormAccount = document.querySelector( '.give-stripe-per-form-default-account' );
	const perAccountEdits = Array.from( document.querySelectorAll( '.give-stripe-account-edit-name' ) );
	const perAccountUpdates = Array.from( document.querySelectorAll( '.give-stripe-account-update-name' ) );
	const perAccountCancels = Array.from( document.querySelectorAll( '.give-stripe-account-cancel-name' ) );
	const accountManagerError = document.getElementById( 'give-stripe-account-manager-errors' );

	// These fn calls will JSON format the text areas for Stripe fields stylings under Advanced tab.
	giveStripeJsonFormattedTextarea( stripeStylesBase );
	giveStripeJsonFormattedTextarea( stripeStylesEmpty );
	giveStripeJsonFormattedTextarea( stripeStylesInvalid );
	giveStripeJsonFormattedTextarea( stripeStylesComplete );
	giveStripeJsonFormattedTextarea( stripeCustomFonts );

	/**
	 * Edit Stripe Account Cancel
	 *
	 * On clicking "Cancel" link on account name will revert to edit link and
	 * won't do any changes to account name
	 *
	 *  @since 2.7.0
	 */
	if ( null !== perAccountCancels ) {
		perAccountCancels.forEach( ( perAccountCancel ) => {
			perAccountCancel.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				const updateElement = e.target.previousElementSibling;
				const editElement = updateElement.previousElementSibling;
				const accountElement = e.target.parentNode.parentNode.querySelector( 'input[name="account_name"]' );
				const accountName = ( null !== accountElement ) ? accountElement.value : '';

				e.target.parentNode.parentNode.querySelector( '.give-stripe-account-name' ).textContent = accountName;
				e.target.classList.add( 'give-hidden' );
				updateElement.classList.add( 'give-hidden' );
				editElement.classList.remove( 'give-hidden' );
			} );
		} );
	}

	/**
	 * Edit Stripe Account Name
	 *
	 * On clicking "Edit" link on account name will show text fields
	 * to update account name.
	 *
	 *  @since 2.7.0
	 */
	if ( null !== perAccountEdits ) {
		perAccountEdits.forEach( ( perAccountEdit ) => {
			perAccountEdit.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				const updateElement = e.target.nextElementSibling;
				const accountName = e.target.parentNode.previousElementSibling.textContent.trim();
				const inputElement = document.createElement( 'input' );

				inputElement.type = 'text';
				inputElement.name = 'account_name';
				inputElement.value = accountName;

				e.target.parentNode.previousElementSibling.textContent = '';
				e.target.parentNode.previousElementSibling.append( inputElement );

				e.target.classList.add( 'give-hidden' );
				updateElement.classList.remove( 'give-hidden' );
				updateElement.nextElementSibling.classList.remove( 'give-hidden' );
			} );
		} );
	}

	/**
	 * Update Stripe Account Name
	 *
	 * On changing the account name and clicking on the "Update" link will
	 * update the account name of a particular Stripe account.
	 *
	 * @since 2.7.0
	 */
	if ( null !== perAccountUpdates ) {
		perAccountUpdates.forEach( ( perAccountUpdate ) => {
			perAccountUpdate.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				const accountSlug = e.target.getAttribute( 'data-account' );
				const accountNameElement = e.target.parentNode.previousElementSibling;
				const newAccountName = accountNameElement.querySelector( 'input[name="account_name"]' ).value;

				const xhr = new XMLHttpRequest();
				const formData = new FormData();
				const editElement = e.target.previousElementSibling;

				formData.append( 'action', 'give_stripe_update_account_name' );
				formData.append( 'account_slug', accountSlug );
				formData.append( 'new_account_name', newAccountName );

				xhr.open( 'POST', ajaxurl );
				xhr.onload = function() {
					const response = JSON.parse( xhr.response );
					let notice = '';

					if ( xhr.status === 200 && response.success ) {
						notice = `<div class="give-notice notice inline success notice-success"><p>${response.data.message}</p></div>`;
						accountNameElement.innerHTML = response.data.name;
						e.target.classList.add( 'give-hidden' );
						e.target.setAttribute( 'data-account', response.data.slug );
						editElement.classList.remove( 'give-hidden' );
					} else {
						notice = `<div class="give-notice notice inline error notice-error"><p>${response.data.message}</p></div>`;
					}
					accountManagerError.innerHTML = notice;
				};
				xhr.send( formData );
			} );
		} );
	}

	/**
	 * Show/Hide Per-Form fields
	 *
	 * When a user want to add per-form Stripe account, this code will help
	 * toggle the Stripe account list on clicking 'Customize'.
	 *
	 * @since 2.7.0
	 */
	if ( null !== perFormOptions ) {
		perFormOptions.forEach( ( formOption ) => {
			formOption.addEventListener( 'change', ( e ) => {
				if ( 'enabled' === e.target.value ) {
					perFormAccount.classList.remove( 'give-hidden' );
				} else {
					perFormAccount.classList.add( 'give-hidden' );
				}
			} );
		} );
	}

	/**
	 * Set Default Stripe Account
	 *
	 * This will be used to set any non-default Stripe account from the list
	 * to set that particular Stripe account as default.
	 *
	 * @since 2.7.0
	 */
	if ( null !== setStripeDefaults ) {
		setStripeDefaults.forEach( ( setStripeDefault ) => {
			setStripeDefault.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				const xhr = new XMLHttpRequest();
				const formData = new FormData();

				formData.append( 'action', 'give_stripe_set_account_default' );
				formData.append( 'account_slug', e.target.getAttribute( 'data-account' ) );
				xhr.open( 'POST', ajaxurl );
				xhr.onload = function() {
					const response = JSON.parse( xhr.response );
					if ( xhr.status === 200 && response.success ) {
						window.location.href = e.target.getAttribute( 'data-url' );
					}
				};
				xhr.send( formData );
			} );
		} );
	}

	/**
	 * Connection Type Toggle for Stripe Account
	 *
	 * This will help toggle between connect and manual fields of Stripe
	 * using the "Connection Type" radio fields.
	 *
	 * @since 2.7.0
	 */
	if ( null !== connectionTypes && null !== selectedConnectionType ) {
		giveStripeConnectionTypeToggle( selectedConnectionType.value );

		connectionTypes.forEach( ( connectionType ) => {
			connectionType.addEventListener( 'change', ( e ) => {
				giveStripeConnectionTypeToggle( e.target.value );
			} );
		} );
	}

	/**
	 * Toggle for Stripe Connection Type.
	 *
	 * @param {string} $value
	 *
	 * @since 2.7.0
	 */
	function giveStripeConnectionTypeToggle( $value ) {
		if ( 'connect' === $value ) {
			manualFields.map( ( element ) => {
				element.classList.add( 'give-hidden' );
			} );
			connectField.classList.remove( 'give-hidden' );
		} else {
			manualFields.map( ( element ) => {
				element.classList.remove( 'give-hidden' );
			} );
			connectField.classList.add( 'give-hidden' );
		}
	}

	/**
	 * Show/Hide SEPA Icon Style Settings.
	 *
	 * This will show/hide the Icon Style settings for SEPA.
	 */
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

	/**
	 * Show/Hide Mandate Textarea Settings for SEPA.
	 *
	 * This will show/hide the Mandate Textarea settings for SEPA.
	 */
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

	/**
	 * Show/Hide BECS Icon Style Settings.
	 *
	 * This will show/hide the Icon Style settings for BECS.
	 */
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

	/**
	 * Show/Hide Mandate Textarea Settings for BECS.
	 *
	 * This will show/hide the Mandate Textarea settings for BECS.
	 */
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

	if ( null !== disconnectBtns ) {
		disconnectBtns.forEach( ( disconnectBtn ) => {
			disconnectBtn.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				new Give.modal.GiveConfirmModal( {
					type: 'alert',
					classes: {
						modalWrapper: 'give-modal--warning',
					},
					modalContent: {
						title: Give.fn.getGlobalVar( 'disconnect_stripe_title' ),
						desc: e.target.getAttribute( 'data-disconnect-message' ),
					},
					successConfirm: () => {
						window.location.href = e.target.getAttribute( 'href' );
					},
				} ).render();
			} );
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
