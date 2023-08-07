/**
 * External dependencies.
 */
import {__} from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import { GiveConfirmModal, GiveErrorAlert } from '../../plugins/modal';

window.addEventListener( 'DOMContentLoaded', function() {
	const donationStatus = document.getElementById( 'give-payment-status' ),
		  onBoardingButtons = document.querySelectorAll( 'button.js-give-paypal-on-boarding-handler' ),
		  disconnectPayPalAccountButtons = document.querySelectorAll( '.js-give-paypal-disconnect-paypal-account' ),
		  countryField = document.getElementById( 'paypal_commerce_account_country' ),
		  paypalModalObserver = new MutationObserver( function( mutationsRecord ) {
			  mutationsRecord.forEach( function( record ) {
			  	record.removedNodes.forEach( function( node ) {
					if ( 'PPMiniWin' === node.getAttribute( 'id' ) ) {
						const paypalErrorQuickHelp = document.getElementById( 'give-paypal-onboarding-trouble-notice' );
						paypalErrorQuickHelp && paypalErrorQuickHelp.classList.remove( 'give-hidden' );
					}
				} );
			  } );
		  } );

        const checkVerifiedIcon =   "<svg width=\"16\" height=\"16\" viewBox=\"0 0 16 16\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n" +
            "<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M7.06407 0.984993C7.34006 0.772558 7.6701 0.666583 8.00008 0.666664C8.33006 0.666583 8.6601 0.772558 8.93609 0.984993L9.71851 1.58576L10.699 1.45673C11.3891 1.3658 12.0529 1.75031 12.3191 2.39199L12.6958 3.30444L13.6069 3.68059L13.6082 3.68113C14.2499 3.94736 14.6343 4.61155 14.5433 5.30156L14.4144 6.28125L15.0155 7.06417C15.1744 7.27101 15.2738 7.50834 15.3135 7.75332C15.3425 7.93195 15.3398 8.11464 15.3054 8.29254C15.2611 8.52121 15.1644 8.74192 15.0155 8.93582L14.4144 9.71874L14.5433 10.6984C14.6343 11.3884 14.2499 12.0526 13.6082 12.3189L13.6069 12.3194L12.6958 12.6956L12.3191 13.608C12.0529 14.2497 11.3891 14.6342 10.699 14.5433L9.71851 14.4142L8.93609 15.015C8.6601 15.2274 8.33006 15.3334 8.00008 15.3333C7.6701 15.3334 7.34006 15.2274 7.06407 15.015L6.28166 14.4142L5.30112 14.5433C4.61107 14.6342 3.94724 14.2497 3.68104 13.608L3.30436 12.6956L2.39328 12.3194L2.39198 12.3189C1.75029 12.0526 1.3659 11.3884 1.45682 10.6984L1.58572 9.71874L0.984629 8.93582C0.81173 8.71067 0.709272 8.44939 0.677476 8.18144C0.631358 7.79295 0.733966 7.39059 0.984629 7.06417L1.58572 6.28125L1.45682 5.30156C1.3659 4.61155 1.75029 3.94736 2.39198 3.68113L2.39328 3.68059L3.30436 3.30444L3.68104 2.39199C3.94724 1.75031 4.61107 1.3658 5.30112 1.45673L6.28166 1.58576L7.06407 0.984993ZM10.8048 6.80474C11.0652 6.54439 11.0652 6.12228 10.8048 5.86193C10.5445 5.60158 10.1224 5.60158 9.86201 5.86193L7.33342 8.39052L6.47149 7.52859C6.21114 7.26824 5.78903 7.26824 5.52868 7.52859C5.26833 7.78894 5.26833 8.21105 5.52868 8.4714L6.86201 9.80474C7.12236 10.0651 7.54447 10.0651 7.80482 9.80474L10.8048 6.80474Z\" fill=\"#459948\"/>\n" +
            "</svg>";

    // This object will check if a class added to body or not.
    // If class added that means modal opened.
    // If class removed that means modal closed.
    paypalModalObserver.observe( document.querySelector( 'body' ), { attributes: true, childList: true } );

	if ( donationStatus ) {
		donationStatus.addEventListener( 'change', ( event ) => {
			const paypalDonationsCheckbox = document.getElementById( 'give-paypal-commerce-opt-refund' );

			if ( null === paypalDonationsCheckbox ) {
				return;
			}

			paypalDonationsCheckbox.checked = false;

			// If donation status is complete, then show refund checkbox
			if ( 'refunded' === event.target.value ) {
				document.getElementById( 'give-paypal-commerce-opt-refund-wrap' ).style.display = 'block';
			} else {
				document.getElementById( 'give-paypal-commerce-opt-refund-wrap' ).style.display = 'none';
			}
		} );
	}

	if ( window.location.search.match( /paypal-commerce-account-connected=1/i ) ) {
		const pciWarnings = window.givePayPalCommerce.translations.pciComplianceInstructions
			.map( instruction => `<li>${ instruction }</li>` )
			.join( '' );

		// eslint-disable-next-line no-undef
		new Give.modal.GiveSuccessAlert( {
			classes: {
				modalWrapper: 'paypal-commerce-connect',
				cancelBtn: 'give-button--primary',
			},
			modalContent: {
				title: window.givePayPalCommerce.translations.connectSuccessTitle,
				body: `
					<div class="give-modal__description">
						<p>${ window.givePayPalCommerce.translations.pciWarning }</p>
						<ul>${ pciWarnings }</ul>
					</div>
				`.trim(),
				cancelBtnTitle: Give.fn.getGlobalVar( 'confirm' ),
			},
			closeOnBgClick: true
		} ).render();

		// Update URL in browser address without reloading the page.
		let newUrl = Give.fn.removeURLParameter( window.location.href, 'paypal-commerce-account-connected' );
		history.pushState( {}, '', newUrl );
	}

	if ( onBoardingButtons.length ) {
        onBoardingButtons.forEach( function( onBoardingButton ) {
            onBoardingButton.addEventListener( 'click', function( evt ) {
                evt.preventDefault();

                let canOptInForAdvancedCardProcessing  =  false;
                let connectionAccountType = null;
                const mode = onBoardingButton.getAttribute( 'data-mode' );
                const countryCode = countryField.value;
                const container = {
                    $el_container: onBoardingButton.closest( 'td.give-forminp' ),
                    removeErrors: () => {
                        const errorsContainer = container.$el_container
                            .querySelector( '.paypal-message-template' );

                        if ( errorsContainer ) {
                            errorsContainer.parentElement.remove();
                        }
                    }
                }
                const buttonState = {
                    enable: () => {
                        onBoardingButton.disabled = false;
                        evt.target.innerText = onBoardingButton.getAttribute( 'data-initial-label' );
                    },
                    disable: () => {
                        // Preserve initial label.
                        if ( ! onBoardingButton.hasAttribute( 'data-initial-label' ) ) {
                            onBoardingButton.setAttribute( 'data-initial-label', onBoardingButton.innerText );
                        }

                        onBoardingButton.disabled = true;
                        evt.target.innerText = Give.fn.getGlobalVar( 'loader_translation' ).processing;
                    },
                };
                const paypalErrorQuickHelp = document.getElementById( 'give-paypal-onboarding-trouble-notice' );
                const ajaxRequest = () => {
                    {
                        // Request partner obboarding link.
                        fetch( ajaxurl + `?action=give_paypal_commerce_get_partner_url&countryCode=${countryCode}&mode=${mode}&accountType=${connectionAccountType  ?? 'EXPRESS_CHECKOUT'}` )
                            .then( response => response.json() )
                            .then( function( res ) {
                                if ( true === res.success ) {
                                    const payPalLink = document.querySelector( '[data-paypal-button]' );

                                    // Dynamically set callback function name.
                                    payPalLink.setAttribute(
                                        'data-paypal-onboard-complete',
                                        'live' === mode
                                            ? 'giveLivePayPalOnBoardedCallback'
                                            : 'giveSandboxPayPalOnBoardedCallback'
                                    );

                                    // Set PayPal button link (Partener link).
                                    payPalLink.href = `${ res.data.partnerLink }&displayMode=minibrowser`;

                                    payPalLink.click();
                                }

                                buttonState.enable();
                            } )
                            // Request troubleshooting help message.
                            .then( function() {
                                fetch( ajaxurl + '?action=give_paypal_commerce_onboarding_trouble_notice' )
                                    .then( response => response.json() )
                                    .then( function( res ) {
                                        if ( true === res.success ) {
                                            function createElementFromHTML( htmlString ) {
                                                const div = document.createElement( 'div' );
                                                div.innerHTML = htmlString.trim();
                                                return div.firstChild;
                                            }

                                            const buttonContainer = container.$el_container.querySelector( '.connect-button-wrap' );
                                            buttonContainer.append( createElementFromHTML( res.data ) );
                                        }
                                    } );
                            } );
                    }
                };

                // eslint-disable-next-line no-undef
                const modalBody  =  `
                    <div class="give-modal__description">
                        <p class="welcome-text">Select account type for connection</p>
                        <p>
                            <label for="paypal_donations_connection_account_type_ppcp">
                                <input type="radio"
                                    name="paypal_donations_connection_account_type"
                                    id="paypal_donations_connection_account_type_ppcp"
                                    value="PPCP">&nbsp;${ __( 'Advancded Card Processing', 'givewp') }
                            </label>
                        </p>
                        <ul>
                            <li><span class="icon">${checkVerifiedIcon}</span>${__( 'Accept Credit & Debit Cards', 'givewp')}</li>
                            <li><span class="icon">${checkVerifiedIcon}</span>${__( 'Seller Protection', 'givewp')}</li>
                        </ul>
                        <p>
                            <label for="paypal_donations_connection_account_type_express_checkout">
                                <input type="radio"
                                    name="paypal_donations_connection_account_type"
                                    id="paypal_donations_connection_account_type_express_checkout"
                                    value="EXPRESS_CHECKOUT">&nbsp;${__( 'Standard Card Processing', 'give')}
                            </label>
                            <span>${__( 'Requires Application Approval', 'give')}</span>
                        </p>
                         <ul class="flex2x2">
                            <li><span class="icon">${checkVerifiedIcon}</span>${__( 'Accept Credit & Debit Cards', 'give')}</li>
                            <li><span class="icon">${checkVerifiedIcon}</span>${__( 'Seller Protection', 'give')}</li>
                            <li><span class="icon">${checkVerifiedIcon}</span>${__( 'Fraud Protection', 'give')}</li>
                            <li><span class="icon">${checkVerifiedIcon}</span>${__( 'Chargeback Protection', 'give')}</li>
                        </ul>
                        <div class="give-field-description">
                            <a href="https://docs.givewp.com/connection-comparison" target="_blank">
                                ${__('Read more about the connection types', 'give')}
                            </a>
                        </div>
                    </div>
                `.trim();

                const modal = new Give.modal.GiveConfirmModal({
                        classes: {
                            modalWrapper: 'paypal-commerce-connection-account-type-selection-modal',
                        },
                        modalContent: {
                            title: __( 'PayPal Connection', 'givewp' ),
                            body: modalBody,
                        },
                        closeOnBgClick: true,
                        callbacks: {
                            open: () => {
                                // Disable confirm button in modal till user selects account type.
                                document.querySelector('.give-popup-confirm-button').disabled = true;

                                // Add event listener to enable confirm button when user selects account type.
                                document.querySelectorAll('input[name="paypal_donations_connection_account_type"]')
                                    .forEach( (radioField) => {
                                        radioField.addEventListener('click', function () {
                                            document.querySelector('.give-popup-confirm-button').disabled = false;
                                        })
                                    })
                            },
                            close: () => {
                                // Reset connection account type.
                                connectionAccountType = null;

                                // Remove errors.
                                container.removeErrors();

                                // Enable button if admin available for both conneciton account types but did not select any.
                                if(!canOptInForAdvancedCardProcessing){
                                    buttonState.enable();
                                }

                                // Hide PayPal quick help message.
                                paypalErrorQuickHelp && paypalErrorQuickHelp.remove();
                            }
                        },
                        successConfirm: () => {
                            const radioField = document.querySelector('input[name="paypal_donations_connection_account_type"]:checked');
                                                    radioField && ( connectionAccountType = radioField.value );

                                                    // Modal will only open when admin available for both conneciton account types.
                            // So we only need to validate if admin selected any account type or not.
                            canOptInForAdvancedCardProcessing =   givePayPalCommerce.accountTypes.includes( connectionAccountType );

                            // Exit if admin available for both conneciton account types but did not select any.
                            if(! canOptInForAdvancedCardProcessing){
                                return;
                            }

                                                    ajaxRequest();
                        }
                });

                container.removeErrors();
                buttonState.disable();

                // Hide PayPal quick help message.
                paypalErrorQuickHelp && paypalErrorQuickHelp.remove();

                // Ask for connection account type if admin selected acount which is available for PPCP and Express Checkout account.
                // Request parther link otherwise which will fetch onboarding link for Express Checkout account type.
                if( givePayPalCommerce.countriesAvailableForAdvanceConnection.includes( countryCode ) ) {
                    modal.render();
                } else{
                    ajaxRequest();
                }

                return false;
            } );
        })
    }

    if (disconnectPayPalAccountButtons.length) {
        disconnectPayPalAccountButtons.forEach(function (disconnectPayPalAccountButton) {
            disconnectPayPalAccountButton.addEventListener('click', function (evt) {
                evt.preventDefault();

                const button = evt.target;
                const ButtonContainerEl = button.closest('div.connect-button-wrap');
                const connectionSettingEl = ButtonContainerEl.querySelector('div.connection-setting');
                const disConnectionSettingEl = ButtonContainerEl.querySelector('div.disconnection-setting');
                let isConfirmed = false;
                const disconnectPayPalAccountFn = () => {
                    const formData = new FormData();
                    const requestData = {};

                    // Do nothing if user cancel the confirmation.
                    if (!isConfirmed) {
                        return;
                    }

                    formData.append('action', 'give_paypal_commerce_disconnect_account');
                    formData.append('mode', button.getAttribute('data-mode'));

                    requestData.method = 'POST';
                    requestData.body = formData;

                    // Send request to disconnect PayPal account.
                    fetch(ajaxurl, requestData)
                        .then(response => response.json())
                        .then(function (response) {
                            if (!response.success) {
                                // Show error message.
                                new GiveErrorAlert({
                                    modalContent: {
                                        desc: response.data.error,
                                    }
                                }).render();

                                return;
                            }

                            connectionSettingEl.classList.remove('give-hidden');
                            disConnectionSettingEl.classList.add('give-hidden');
                        });

                };

                // Show confirmation modal.
                new GiveConfirmModal({
                    modalContent: {
                        title: givePayPalCommerce.translations.confirmPaypalAccountDisconnection,
                        desc: givePayPalCommerce.translations.disconnectPayPalAccount,
                    },
                    successConfirm: () => isConfirmed = true,
                    callbacks: {
                        afterClose: () => disconnectPayPalAccountFn()
                    }
                }).render()

                return false;
            });
        });
    }
} );
