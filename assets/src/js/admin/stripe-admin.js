/**
 * Give - Stripe Core Admin JS
 *
 * @since 2.5.0
 */
import { __, sprintf } from '@wordpress/i18n'

import '../../../../src/PaymentGateways/resources/js/stripe-account-manager/set-default-account-action'
import '../../../../src/PaymentGateways/resources/js/stripe-account-manager/disconnect-stripe-account-action'
import '../../../../src/PaymentGateways/resources/js/stripe-account-manager/customize-stripe-account-validation'

window.addEventListener( 'DOMContentLoaded', function() {
	const stripeFonts = document.querySelectorAll( 'input[name="stripe_fonts"]' );
	const stripeStylesBase = document.getElementById( 'stripe_styles_base' );
	const stripeStylesEmpty = document.getElementById( 'stripe_styles_empty' );
	const stripeStylesInvalid = document.getElementById( 'stripe_styles_invalid' );
	const stripeStylesComplete = document.getElementById( 'stripe_styles_complete' );
	const stripeCustomFonts = document.getElementById( 'stripe_custom_fonts' );
	const donationStatus = document.getElementById( 'give-payment-status' );
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
	const perFormOptions = Array.from( document.querySelectorAll( 'input[name="give_stripe_per_form_accounts"]' ) );
	const perFormAccount = document.querySelector( '.give-stripe-manage-account-options' );
	const creditCardFieldFormatOptions = document.querySelectorAll('#give-settings-section-group-credit-card .give-stripe-cc-option-field')
	const editStripeStatementDescriptor = document.querySelectorAll('#give-settings-section-group-accounts .give-stripe-edit-statement-descriptor-btn')

	// These fn calls will JSON format the text areas for Stripe fields stylings under Advanced tab.
	giveStripeJsonFormattedTextarea( stripeStylesBase );
	giveStripeJsonFormattedTextarea( stripeStylesEmpty );
	giveStripeJsonFormattedTextarea( stripeStylesInvalid );
	giveStripeJsonFormattedTextarea( stripeStylesComplete );
	giveStripeJsonFormattedTextarea( stripeCustomFonts );

	/**
	 * Show/Hide Per-Form fields
	 *
	 * When a user want to add per-form Stripe account, this code toggles the Stripe account list on clicking 'Customize'.
	 *
	 * @since 2.7.0
	 */
	if ( null !== perFormOptions ) {
		let perFormOptionFieldContainers = document.querySelectorAll('#stripe_form_account_options .give-stripe-per-form-option-field');

		perFormOptions.forEach( ( formOption ) => {
			formOption.addEventListener( 'change', ( e ) => {
				perFormOptionFieldContainers.forEach( el => {
					el.classList.remove('give-stripe-boxshadow-option-wrap__selected')
					el.querySelector('input[name="give_stripe_per_form_accounts"]').setAttribute( 'checked', '' );
				} );

				e.target.parentElement.parentElement.classList.add('give-stripe-boxshadow-option-wrap__selected');
				e.target.setAttribute( 'checked', 'checked' );

				if ( 'enabled' === e.target.value ) {
					perFormAccount.classList.remove( 'give-hidden' );
				} else {
					perFormAccount.classList.add( 'give-hidden' );
				}
			} );
		} );
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
			new Give.modal.GiveConfirmModal(
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

	/**
	 * Click on hidden checkbox value when select on credit card format type.
	 *
	 * @since 2.14.0
	 */
	if( creditCardFieldFormatOptions.length ) {
		creditCardFieldFormatOptions.forEach(function( inputFieldContainer ){
			inputFieldContainer.addEventListener('click', function (){
				creditCardFieldFormatOptions.forEach(function(container){
					container.classList.remove('give-stripe-boxshadow-option-wrap__selected');
					container.querySelector('input[name="stripe_cc_fields_format"]').setAttribute( 'checked', '' );
				})

				inputFieldContainer.querySelector('input[name="stripe_cc_fields_format"]')
					.setAttribute( 'checked', 'checked' );
				inputFieldContainer.classList.add('give-stripe-boxshadow-option-wrap__selected');
			})
		})
	}

    if (editStripeStatementDescriptor.length) {
        let formTemplate = `
            <input type="text" minlength="5" maxlength="22">
            <button class="button-primary" disabled>${__('Save', 'give')}</button>
            <button class="button-secondary">${__('Cancel', 'give')}</button>`;

        // Statement descriptor text will be validate on basis of Stripe requirements.
        // Read more about requirements: https://stripe.com/docs/statement-descriptors#requirements
        let isValidaStatementDescriptor =  ( text ) => {
            if(
                22 < text.length ||
                text.length < 5 ||
                ! isNaN(text)
            ){
                return false;
            }

            return 0 === text.split('')
                .filter( (char) => ['*', '\'', '"', '\\', '<', '>'].includes(char) ).length
        }

        editStripeStatementDescriptor.forEach((actionLink) => {
            actionLink.addEventListener(
                'click',
                (e) => {
                    e.preventDefault();

                    let container = actionLink.closest('.give-stripe-connect-data-field'),
                        containerDisplayStylePropertyValue = container.style.display;

                    // Enable statement descriptor editing mode.
                    container.insertAdjacentHTML('afterend', formTemplate);
                    container.style.display = 'none';

                    let inputField = container.nextElementSibling,
                        saveButton = inputField.nextElementSibling,
                        cancelButton = saveButton.nextElementSibling;

                    // Add style.
                    inputField.value = container.childNodes[0].nodeValue.trim();
                    inputField.style.display = 'block';
                    inputField.style.marginBottom = '10px';
                    saveButton.style.marginRight = '5px';

                    // Add events.
                    inputField.addEventListener(
                        'keyup',
                        (e) => {
                            let newStatementDescriptor = inputField.value.trim(),
                                savedStatementDescriptor = container.childNodes[0].nodeValue.trim();

                            if (
                                !newStatementDescriptor ||
                                newStatementDescriptor === savedStatementDescriptor) {
                                saveButton.disabled = true;
                                return;
                            }

                            saveButton.disabled = false;
                        }
                    );

                    cancelButton.addEventListener(
                        'click',
                        (e) => {
                            e.preventDefault();

                            inputField.remove();
                            saveButton.remove();
                            cancelButton.remove();

                            container.style.display = containerDisplayStylePropertyValue;
                        });

                    saveButton.addEventListener(
                        'click',
                        (e) => {
                            e.preventDefault();

                            let newStatementDescriptorText = inputField.value.trim();
                            let actionUrl = `${container.getAttribute('data-action-url')}&statement-descriptor=${encodeURIComponent(newStatementDescriptorText)}`

                            if( ! isValidaStatementDescriptor(newStatementDescriptorText) ) {
                                new Give.modal.GiveErrorAlert({
                                    modalContent:{
                                        title: __( 'Invalid Statement Descriptor Text', 'give'),
                                        desc: sprintf(
                                            '%s <br>%s <br>- %s<br>- %s<br>- %s<br>- %s<br><br><a href="%s" target="_blank">%s</a>',
                                            __( 'Please enter a valid Stripe statement descriptor.', 'give'),
                                            __( 'List of important Stripe statement descriptor text requirements:', 'give'),
                                            __( 'Contains only Latin characters.', 'give'),
                                            __( 'Contains between 5 and 22 characters, inclusive.', 'give'),
                                            __( 'Contains at least one letter.', 'give'),
                                            __( 'Does not contain any of the special characters < > \\ \' " *.', 'give'),
                                            'https://stripe.com/docs/statement-descriptors#requirements',
                                            __( 'Read more about stripe statement descriptor text requirements.', 'give'),
                                        ),
                                    }
                                }).render();
                                return;
                            }

                            fetch(actionUrl)
                                .then(response => response.json())
                                .then(response => {
                                    if( ! response.success ){
                                        new Give.modal.GiveErrorAlert({
                                            modalContent:{
                                                title: __( 'Invalid Stripe Statement Descriptor Text', 'give'),
                                                desc: sprintf(
                                                    '%s %s<br><br><a href="%s" target="_blank">%s</a>',
                                                    __( 'We are unable to update Stripe statement descriptor.', 'give'),
                                                    response.data.errorMessage,
                                                    'https://stripe.com/docs/statement-descriptors#requirements',
                                                    __( 'Read more about stripe statement descriptor text requirements.', 'give'),
                                                ),
                                            }
                                        }).render();

                                        return;
                                    }

                                    // Update complete. Add new text for display and exit editing mode.
                                    container.childNodes[0].nodeValue = newStatementDescriptorText
                                    cancelButton.click();
                                });
                        });
                })
        })
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
