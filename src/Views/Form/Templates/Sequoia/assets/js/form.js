/* globals jQuery, Give */
( function( $ ) {
	const templateOptions = window.sequoiaTemplateOptions;
	const $container = $( '.give-embed-form' );
	const $advanceButton = $( '.advance-btn', $container );
	const $backButton = $( '.back-btn' );
	const $navigatorTitle = $( '.give-form-navigator .title' );
	let gatewayAnimating = false;

	const navigator = {
		currentStep: templateOptions.introduction.enabled === 'enabled' ? 0 : 1,
		animating: false,
		goToStep: ( step ) => {
			// Adjust body height before animating step, to prevent choppy iframe resizing
			// Compare next step to current step, and increase body height if next step is taller.
			const nextStepHeight = steps[ step ].title ? $( steps[ step ].selector ).height() + 50 : $( steps[ step ].selector ).height();
			const currentStepHeight = steps[ navigator.currentStep ].title ? $( steps[ navigator.currentStep ].selector ).height() + 50 : $( steps[ navigator.currentStep ].selector ).height();
			if ( nextStepHeight > currentStepHeight ) {
				$( '.give-form-templates' ).css( 'min-height', `${ nextStepHeight + 123 }px` );
			} else {
				// Delay setting body height if next step is shorter than current step
				setTimeout( function() {
					$( '.give-form-templates' ).css( 'min-height', `${ nextStepHeight + 123 }px` );
				}, 200 );
			}

			$( '.step-tracker' ).removeClass( 'current' );
			$( '.step-tracker[data-step="' + step + '"]' ).addClass( 'current' );

			if ( templateOptions.introduction.enabled === 'disabled' ) {
				if ( $( '.step-tracker' ).length === 3 ) {
					$( '.step-tracker' ).remove();
				}

				step = step > 0 ? step : 1;
				if ( step === 1 ) {
					$( '.back-btn', $container ).hide();
				} else {
					$( '.back-btn', $container ).show();
				}

				$( '.give-form-navigator', $container ).addClass( 'nav-visible' );
				$( steps[ step ].selector ).css( 'padding-top', '50px' );
			} else if ( step === 0 ) {
				$( '.give-form-navigator', $container ).removeClass( 'nav-visible' );
				$( steps[ step ].selector ).css( 'padding-top', '' );
			} else {
				$( '.give-form-navigator', $container ).addClass( 'nav-visible' );
				$( steps[ step ].selector ).css( 'padding-top', '50px' );
			}

			if ( steps[ step ].title ) {
				$navigatorTitle.text( steps[ step ].title );
			}

			const hide = steps.map( ( obj, index ) => {
				if ( index === step || index === navigator.currentStep ) {
					return null;
				}
				return obj.selector;
			} );
			const hideSelector = hide.filter( Boolean ).join( ', ' );

			$( hideSelector ).hide();

			if ( navigator.currentStep !== step ) {
				const directionClasses = 'slide-in-right slide-in-left slide-out-right slide-out-left';
				const outDirection = navigator.currentStep < step ? 'left' : 'right';
				const inDirection = navigator.currentStep < step ? 'right' : 'left';
				$( steps[ navigator.currentStep ].selector ).removeClass( directionClasses ).addClass( `slide-out-${ outDirection }` );
				$( steps[ step ].selector ).show().removeClass( directionClasses ).addClass( `slide-in-${ inDirection }` );
			}
			navigator.currentStep = step;
		},
		init: () => {
			steps.forEach( ( step ) => {
				if ( step.setup !== undefined ) {
					step.setup();
				}
				$( step.selector ).css( 'position', 'absolute' );
			} );
			$advanceButton.on( 'click', function( e ) {
				e.preventDefault();
				navigator.forward();
			} );
			$backButton.on( 'click', function( e ) {
				e.preventDefault();
				navigator.back();
			} );
			$( '.step-tracker' ).on( 'click', function( e ) {
				e.preventDefault();
				navigator.goToStep( parseInt( $( e.target ).attr( 'data-step' ) ) );
			} );
			setupHeightChangeCallback( function( height ) {
				if ( gatewayAnimating === false ) {
					$( '.form-footer' ).css( 'transition', 'margin-top 0.2s ease' );
				} else {
					$( '.form-footer' ).css( 'transition', '' );
				}
				$( '.form-footer' ).css( 'margin-top', `${ height }px` );
			} );
			navigator.goToStep( getInitialStep() );
		},
		back: () => {
			const prevStep = navigator.currentStep !== 0 ? navigator.currentStep - 1 : 0;
			navigator.goToStep( prevStep );
			navigator.currentStep = prevStep;
		},
		forward: () => {
			const nextStep = navigator.currentStep !== null ? navigator.currentStep + 1 : 1;
			navigator.goToStep( nextStep );
			navigator.currentStep = nextStep;
		},
	};

	const steps = [
		{
			id: 'introduction',
			title: null,
			selector: '.give-section.introduction',
			label: templateOptions.introduction.donate_label,
			showErrors: false,
		},
		{
			id: 'choose-amount',
			title: templateOptions.payment_amount.header_label,
			selector: '.give-section.choose-amount',
			label: templateOptions.payment_amount.next_label,
			showErrors: false,
			setup: () => {
				$( '#give-amount' ).on( 'blur', function() {
					if ( ! Give.form.fn.isValidDonationAmount( $( 'form' ) ) ) {
						$( '.advance-btn' ).attr( 'disabled', true );
					} else {
						$( '.advance-btn' ).attr( 'disabled', false );
					}
				} );
				$( '.give-donation-level-btn' ).each( function() {
					const hasTooltip = $( this ).attr( 'has-tooltip' );
					if ( hasTooltip ) {
						return;
					}

					const value = $( this ).attr( 'value' );
					const text = $( this ).text();
					const symbol = window.give_global_vars.currency_sign;
					const position = window.give_global_vars.currency_pos;

					if ( value !== 'custom' ) {
						const html = position === 'before' ? `<div class="currency">${ symbol }</div>${ value }` : `${ value }<div class="currency">${ symbol }</div>`;
						$( this ).html( html );
					}

					// Setup string to check tooltip label ga
					const compare = position === 'before' ? symbol + value : value + symbol;
					// Setup tooltip unless for custom donation level, or if level label matches value
					if ( value !== 'custom' && text !== compare ) {
						const wrap = `<span class="give-tooltip hint--top hint--bounce ${ text.length < 50 ? 'narrow' : '' }" style="width: 100%" aria-label="${ text.length < 50 ? text : text.substr( 0, 50 ) + '...' }" rel="tooltip"></span>`;
						$( this ).wrap( wrap );
						$( this ).attr( 'has-tooltip', true );
					}
				} );
			},
		},
		{
			id: 'payment',
			title: templateOptions.payment_information.header_label,
			label: templateOptions.payment_information.checkout_label,
			selector: '.give-section.payment',
			showErrors: true,
			setup: () => {
				// Setup payment information screen

				$( '.give-section.payment' ).on( 'click', '.give-cancel-login, .give-checkout-register-cancel', clearLoginNotices );

				// Show Sequoia loader on click/touchend
				$( '.give-section.payment' ).on( 'click touchend', 'input[name="give_login_submit"]', function() {
					//Override submit loader with Sequoia loader
					$( 'input[name="give_login_submit"] + .give-loading-animation' ).removeClass( 'give-loading-animation' ).addClass( 'sequoia-loader spinning' );
				} );

				// Remove purchase_loading text
				window.give_global_vars.purchase_loading = '';

				const testNotice = $( '#give_error_test_mode' );
				$( testNotice ).clone().prependTo( '.give-section.payment' );
				$( testNotice ).remove();

				// Persist the recurring input border when selected
				$( '.give-recurring-period' ).change( function() {
					$( '.give-recurring-donors-choice' ).toggleClass( 'active' );
				} );

				// Persist fee recovery input border when selected
				$( '.give-fee-message-label-text' ).on( 'click touchend', function() {
					$( '.give-fee-recovery-donors-choice' ).toggleClass( 'active' );
				} );

				// Show Sequoia loader on click/touchend
				$( 'body.give-form-templates' ).on( 'click touchend', 'form.give-form input[name="give-purchase"].give-submit', function() {
					//Override submit loader with Sequoia loader
					$( '#give-purchase-button + .give-loading-animation' ).removeClass( 'give-loading-animation' ).addClass( 'sequoia-loader' );

					// Only show spinner if form is valid
					if ( $( 'form' ).get( 0 ).checkValidity() ) {
						$( '.sequoia-loader' ).addClass( 'spinning' );
					}
				} );

				// Go to choose amount step when donation maximum error is clicked
				$( 'body.give-form-templates' ).on( 'click touchend', '#give_error_invalid_donation_maximum', function() {
					// Go to choose amount step
					navigator.goToStep( 1 );
				} );

				// Go to choose amount step when invalid donation error is clicked
				$( 'body.give-form-templates' ).on( 'click touchend', '#give_error_invalid_donation_amount', function() {
					// Go to choose amount step
					navigator.goToStep( 1 );
				} );

				$( '#give-ffm-section' ).on( 'click', handleFFMInput );
				$( '[id*="give-register-account-fields"]' ).on( 'click', handleFFMInput );

				$( '#give-ffm-section input' ).each( function() {
					switch ( $( this ).prop( 'type' ) ) {
						case 'checkbox': {
							if ( $( this ).prop( 'checked' ) ) {
								$( this ).parent().addClass( 'checked' );
							} else {
								$( this ).parent().removeClass( 'checked' );
							}
							break;
						}
						case 'radio': {
							if ( $( this ).prop( 'checked' ) ) {
								$( this ).parent().addClass( 'selected' );
							} else {
								$( this ).parent().removeClass( 'selected' );
							}
							break;
						}
					}
				} );

				//Setup input icons
				setupInputIcon( '#give-first-name-wrap', 'user' );
				setupInputIcon( '#give-email-wrap', 'envelope' );
				setupInputIcon( '#give-company-wrap', 'building' );
				setupInputIcon( '#date_field-wrap', 'calendar-alt' );
				setupInputIcon( '#url_field-wrap', 'globe' );
				setupInputIcon( '#phone_field-wrap', 'phone' );
				setupInputIcon( '#email_field-wrap', 'envelope' );

				// Setup gateway icons
				setupGatewayIcons();

				const observer = new window.MutationObserver( function( mutations ) {
					mutations.forEach( function( mutation ) {
						if ( ! mutation.addedNodes ) {
							return;
						}

						for ( let i = 0; i < mutation.addedNodes.length; i++ ) {
							// do things to your newly added nodes here
							const node = mutation.addedNodes[ i ];

							if ( $( node ).hasClass( 'give_errors' ) && ! $( node ).parent().hasClass( 'payment' ) ) {
								$( node ).clone().prependTo( '.give-section.payment' );
								$( node ).remove();
								$( '.sequoia-loader' ).removeClass( 'spinning' );
							}

							if ( $( node ).attr( 'id' ) === 'give_tributes_address_state' ) {
								const placeholder = $( node ).attr( 'placeholder' );
								$( node ).prepend( `<option selected disabled>${ placeholder }</option>` );
							}

							if ( $( node ).attr( 'name' ) === 'give_tributes_address_state' && $( node ).attr( 'class' ).includes( 'give-input' ) ) {
								$( node ).attr( 'placeholder', $( node ).siblings( 'label' ).text().trim() );
							}

							if ( $( node ).attr( 'id' ) && $( node ).attr( 'id' ).includes( 'give-checkout-login-register' ) ) {
								$( '[id*="give-register-account-fields"]' ).on( 'click', handleFFMInput );
							}

							if ( $( node ).prop( 'tagName' ) && $( node ).prop( 'tagName' ).toLowerCase() === 'select' ) {
								const placeholder = $( node ).attr( 'placeholder' );
								$( node ).prepend( `<option value="" disabled selected>${ placeholder }</option>` );
							}
						}
					} );
				} );

				observer.observe( document.body, {
					childList: true,
					subtree: true,
					attributes: false,
					characterData: false,
				} );
			},
		},
	];

	navigator.init();

	// Check if only a single gateway is enabled
	if ( $( '#give-payment-mode-select' ).css( 'display' ) !== 'none' ) {
		// Move payment information section when document load.
		moveFieldsUnderPaymentGateway( true );

		// Move payment information section when gateway updated.
		$( document ).on( 'give_gateway_loaded', function() {
			moveFieldsUnderPaymentGateway( true );
			$( '#give_purchase_form_wrap' ).slideDown( 200, function() {
				gatewayAnimating = false;
			} );
		} );
		$( document ).on( 'Give:onPreGatewayLoad', function() {
			gatewayAnimating = true;
			$( '#give_purchase_form_wrap' ).slideUp( 200 );
		} );

		// Refresh payment information section.
		$( document ).on( 'give_gateway_loaded', refreshPaymentInformationSection );
	}

	/**
	 * Move form field under payment gateway
	 * @since 2.7.0
	 */
	function moveFieldsUnderPaymentGateway() {
		if ( ! $( '#give-payment-mode-select' ).next().hasClass( 'give-submit' ) ) {
			$( '#give-payment-mode-select' ).after( $( '#give_purchase_form_wrap .give-submit' ) );
		} else {
			$( '#give_purchase_form_wrap .give-submit' ).remove();
		}
		$( '.give-gateway-option-selected' ).after( $( '#give_purchase_form_wrap' ) );
	}

	/**
	 * Refresh payment information section
	 *
	 * @since 2.7.0
	 * @param {boolean} ev Event object
	 * @param {object} response Response object
	 * @param {number} formID Form ID
	 */
	function refreshPaymentInformationSection( ev, response, formID ) {
		if ( navigator.currentStep === 2 ) {
			$( '.give-form-templates' ).css( 'min-height', '' );
		}

		const $form = $( `#${ formID }` );

		// This function will run only for embed donation form.
		// Show payment information section fields.
		if ( $form.parent().hasClass( 'give-embed-form' ) ) {
			const data = {
				action: 'give_cancel_login',
				form_id: $form.find( '[name="give-form-id"]' ).val(),
			};

			// AJAX get the payment fields.
			$.post( Give.fn.getGlobalVar( 'ajaxurl' ), data, function( postResponse ) {
				$form.find( '[id^=give-checkout-login-register]' ).replaceWith( $.parseJSON( postResponse.fields ) );
				$form.find( '[id^=give-checkout-login-register]' ).css( { display: 'block' } );
				$form.find( '.give-submit-button-wrap' ).show();
			} ).done( function() {
				// Trigger float-labels
				window.give_fl_trigger();
			} );
		}
	}

	function setupInputIcon( selector, icon ) {
		$( selector ).prepend( `<i class="fas fa-${ icon }"></i>` );
		$( `${ selector } input, ${ selector } select` ).attr( 'style', 'padding-left: 33px!important;' );
	}

	/**
	 * Loop through gateway li elements and setup fontawesome icons
	 *
	 * @since 2.7.0
	 */
	function setupGatewayIcons() {
		$( '#give-gateway-radio-list li' ).each( function() {
			const value = $( 'input', this ).val();
			let icon;
			switch ( value ) {
				case 'manual':
					icon = 'fas fa-tools';
					break;
				case 'offline':
					icon = 'fas fa-wallet';
					break;
				case 'paypal':
					icon = 'fab fa-paypal';
					break;
				case 'stripe':
					icon = 'far fa-credit-card';
					break;
				case 'stripe_checkout':
					icon = 'far fa-credit-card';
					break;
				case 'stripe_sepa':
					icon = 'fas fa-university';
					break;
				case 'stripe_ach':
					icon = 'fas fa-university';
					break;
				case 'stripe_ideal':
					icon = 'fas fa-university';
					break;
				case 'stripe_becs':
					icon = 'fas fa-university';
					break;
				case 'paypalpro_payflow':
					icon = 'far fa-credit-card';
					break;
				default:
					icon = 'fas fa-hand-holding-heart';
					break;
			}
			$( this ).append( `<i class="${ icon }"></i>` );
		} );
	}

	function setupHeightChangeCallback( callback ) {
		let lastHeight = 0;
		function checkHeightChange() {
			const selector = $( steps[ navigator.currentStep ].selector );
			const changed = lastHeight !== $( selector ).outerHeight();
			if ( changed ) {
				callback( $( selector ).outerHeight() );
				lastHeight = $( selector ).outerHeight();
			}
			window.requestAnimationFrame( checkHeightChange );
		}
		window.requestAnimationFrame( checkHeightChange );
	}

	/**
	 * Get initial step to show donor.
	 *
	 * @since 2.7.0
	 * @returns {number} Step to start on
	 */
	function getInitialStep() {
		return Give.fn.getParameterByName( 'showDonationProcessingError' ) || Give.fn.getParameterByName( 'showFailedDonationError' ) ? 2 : 0;
	}

	/**
	 * Handle updating label classes for FFM radios and checkboxes
	 *
	 * @since 2.7.0
	 * @param {object} evt Reference to FFM input element click event
	 */
	function handleFFMInput( evt ) {
		if ( $( evt.target ).is( 'input' ) ) {
			switch ( $( evt.target ).prop( 'type' ) ) {
				case 'checkbox': {
					$( evt.target ).parent().toggleClass( 'checked' );
					break;
				}
				case 'radio': {
					$( evt.target ).parent().addClass( 'selected' );
					$( evt.target ).parent().siblings().removeClass( 'selected' );
					break;
				}
			}
		}
	}

	function clearLoginNotices() {
		$( '#give_error_must_log_in' ).remove();
	}
}( jQuery ) );
