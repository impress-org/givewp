import { GiveConfirmModal } from '../../plugins/modal';

window.addEventListener( 'DOMContentLoaded', function() {
	const donationStatus = document.getElementById( 'give-payment-status' ),
		  onBoardingButton = document.getElementById( 'js-give-paypal-on-boarding-handler' ),
		  disconnectPayPalAccountButton = document.getElementById( 'js-give-paypal-disconnect-paypal-account' ),
		  connectionSettingContainer = document.querySelector( '#give-paypal-commerce-account-manager-field-wrap .connection-setting' ),
		  disConnectionSettingContainer = document.querySelector( '#give-paypal-commerce-account-manager-field-wrap .disconnection-setting' ),
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
			closeOnBgClick: true,
		} ).render();
	}

	/**
	 * Remove error container.
	 *
	 * @since 2.9.6
	 */
	function removeErrors() {
		const errorsContainer = document.querySelector( '.paypal-message-template' );

		if ( errorsContainer ) {
			errorsContainer.parentElement.remove();
		}
	}

	if ( onBoardingButton ) {
		onBoardingButton.addEventListener( 'click', function( evt ) {
			evt.preventDefault();
			removeErrors();

			const countryCode = countryField.value;
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

			buttonState.disable();

			// Hide paypal quick help message.
			const paypalErrorQuickHelp = document.getElementById( 'give-paypal-onboarding-trouble-notice' );
			paypalErrorQuickHelp && paypalErrorQuickHelp.classList.add( 'give-hidden' );

			fetch( ajaxurl + `?action=give_paypal_commerce_get_partner_url&countryCode=${ countryCode }` )
				.then( response => response.json() )
				.then( function( res ) {
					if ( true === res.success ) {
						const payPalLink = document.querySelector( '[data-paypal-button]' );

						payPalLink.href = `${ res.data.partnerLink }&displayMode=minibrowser`;
						payPalLink.click();

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

								const buttonContainer = document.querySelector( '.connect-button-wrap' );
								paypalErrorQuickHelp && paypalErrorQuickHelp.remove();
								buttonContainer.append( createElementFromHTML( res.data ) );
							}
						} );
				} );

			return false;
		} );
	}

	if ( disconnectPayPalAccountButton ) {
		disconnectPayPalAccountButton.addEventListener( 'click', function( evt ) {
			evt.preventDefault();
			removeErrors();

			new GiveConfirmModal( {
				modalContent: {
					title: givePayPalCommerce.translations.confirmPaypalAccountDisconnection,
					desc: givePayPalCommerce.translations.disconnectPayPalAccount,
				},
				successConfirm: () => {
					connectionSettingContainer.classList.remove( 'give-hidden' );
					disConnectionSettingContainer.classList.add( 'give-hidden' );
					countryField.parentElement.parentElement.classList.remove( 'hide-with-position' );

					fetch( ajaxurl + '?action=give_paypal_commerce_disconnect_account' );
				},
			} ).render();

			return false;
		} );
	}
} );
