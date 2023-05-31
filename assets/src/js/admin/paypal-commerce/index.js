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

		const liveWarning = window.givePayPalCommerce.translations.liveWarning ?
			`<p class="give-modal__description__warning">${ window.givePayPalCommerce.translations.liveWarning }</p>` :
			'';

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
						${ liveWarning }
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
                const paypalErrorQuickHelp = container.$el_container.querySelector( '.give-paypal-onboarding-trouble-notice' );

                container.removeErrors();
                buttonState.disable();

                // Hide PayPal quick help message.
                paypalErrorQuickHelp && paypalErrorQuickHelp.classList.add( 'give-hidden' );

                fetch( ajaxurl + `?action=give_paypal_commerce_get_partner_url&countryCode=${ countryCode }&mode=${ mode }` )
                    .then( response => response.json() )
                    .then( function( res ) {
                        if ( true === res.success ) {
                            const payPalLink = document.querySelector( '[data-paypal-button]' );

                            payPalLink.href = `${ res.data.partnerLink }&displayMode=minibrowser`;
                            payPalLink.click();

                            //PAYPAL.apps.Signup.MiniBrowser.init();

                            // This object will check if a class added to body or not.
                            // If class added that means modal opened.
                            // If class removed that means modal closed.
                            paypalModalObserver.observe( document.querySelector( 'body' ), { attributes: true, childList: true } );
                        }

                        buttonState.enable();
                    } )
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
                                    paypalErrorQuickHelp && paypalErrorQuickHelp.remove();
                                    buttonContainer.append( createElementFromHTML( res.data ) );
                                }
                            } );
                    } );

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

                            let billingSettingContainer = document.querySelector('label[for=\'paypal_commerce_collect_billing_details\']');
                            billingSettingContainer.parentElement.parentElement.classList.add('give-hidden');
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

// @TODO: use  WordPress JS translation function.
