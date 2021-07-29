/* globals jQuery, Give */
( function( $ ) {
	const templateL10n = window.sequoiaTemplateL10n;
	const templateOptions = window.sequoiaTemplateOptions;
	const $container = $( '.give-embed-form' );
	const $advanceButton = $( '.advance-btn', $container );
	const $backButton = $( '.back-btn' );
	const $navigatorTitle = $( '.give-form-navigator .title' );
	const $paymentGatewayContainer = $( '#give-payment-mode-select' );
	let gatewayAnimating = false;
	const { wp: parentWp } = window.parent;

	// Make donation form block selectable in editor
	if ( parentWp && parentWp.data ) {
		$container.on( 'click', function() {
			const blockId = window.frameElement.closest( '.wp-block' ).getAttribute( 'data-block' );
			parentWp.data.dispatch( 'core/block-editor' ).selectBlock( blockId );
		} );
	}

	const navigator = {
		currentStep: templateOptions.introduction.enabled === 'enabled' ? 0 : 1,
		animating: false,
		firstFocus: false,
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

			setTimeout( function() {
				setupTabOrder();
				// Do not auto-focus form on the page load if the first step is disabled
				if ( ! navigator.firstFocus && templateOptions.introduction.enabled === 'disabled' ) {
					return navigator.firstFocus = true;
				}
				if ( steps[ navigator.currentStep ].firstFocus ) {
					$( steps[ navigator.currentStep ].firstFocus ).focus();
				}
			}, 200 );
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

			// If Fee Recovery input is not inside any step, move it to above payment options
			if ( $( '.give-fee-recovery-donors-choice' ).parent().hasClass( 'give-form' ) ) {
				$( '#give_checkout_user_info' ).after( $( '.give-fee-recovery-donors-choice' ) );
			}
			navigator.goToStep( getInitialStep() );

			// Fields API: Run setup for custom checkbox fields.
			const customCheckboxes = document.querySelectorAll( '[data-field-type="checkbox"]' );
			Array.from( customCheckboxes ).forEach( ( el ) => {
				const containerSelector = '[data-field-name="' + el.getAttribute( 'data-field-name' ) + '"]';
				setupCheckbox( {
					container: containerSelector,
					label: containerSelector + ' label',
					input: containerSelector + ' input[type="checkbox"]',
				} );
			} );
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
			tabOrder: [
				'.introduction .advance-btn',
				'.step-tracker',
			],
		},
		{
			id: 'choose-amount',
			title: templateOptions.payment_amount.header_label,
			selector: '.give-section.choose-amount',
			label: templateOptions.payment_amount.next_label,
			showErrors: false,
			tabOrder: [
				'select.give-cs-select-currency',
				'input.give-amount-top',
				'.give-donation-levels-wrap button',
				'.give-recurring-period',
				'.give-recurring-donors-choice-period',
				'.give_fee_mode_checkbox',
				'.choose-amount .advance-btn',
				'.step-tracker',
				'.back-btn',
			],
			firstFocus: '.give-default-level',
			setup: () => {
				// Dynamically set grid columns based on number of buttons
				const buttonCount = $( '.give-donation-level-btn' ).length;
				if ( buttonCount === 1 ) {
					$( '.give-donation-levels-wrap' ).attr( 'style', 'display: none!important;' );
				} else if ( buttonCount % 2 === 0 && buttonCount < 6 ) {
					$( '.give-donation-levels-wrap' ).css( 'grid-template-columns', 'repeat(2, minmax(0, 1fr))' );
				}

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
						const html = position === 'before' ? `<div class="currency currency--before">${ symbol }</div>${ value }` : `${ value }<div class="currency currency--after">${ symbol }</div>`;
						$( this ).html( html );
					}

					// Setup string to check tooltip label ga
					const compare = position === 'before' ? symbol + value : value + symbol;
					// Setup tooltip unless for custom donation level, or if level label matches value
					if ( value !== 'custom' && text !== compare ) {
						const wrap = document.createElement('span');
						wrap.classList.add('give-tooltip', 'hint--top', 'hint--bounce');
						if( text.length < 50 ) {
							wrap.classList.add( 'narrow' );
						}
						wrap.style.width = '100%';
						wrap.setAttribute('aria-label', text.length < 50 ? text : text.substr( 0, 50 ) + '...');
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
			tabOrder: [
				'.payment input, .payment a, .payment button, .payment select, .payment multiselect, .payment textarea, .payment .button',
				'.give-submit',
				'.step-tracker',
				'.back-btn',
			],
			firstFocus: '#give-first',
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

				// Move errors
				$( '.give_error' ).each( function() {
					moveErrorNotice( $( this ) );
				} );

				// Setup anonymous donations opt-in event listeners
				setupCheckbox( {
					container: '#give-anonymous-donation-wrap label',
					label: '#give-anonymous-donation-wrap label',
					input: '#give-anonymous-donation',
				} );

				// Setup recurring donations opt-in event listeners
				setupCheckbox( {
					container: '.give-recurring-donors-choice',
					label: '.give-recurring-donors-choice label',
					input: 'input[name="give-recurring-period"]',
				} );

				// Setup fee recovery opt-in event listeners
				setupCheckbox( {
					container: '.give-fee-recovery-donors-choice',
					label: '.give-fee-message-label-text',
					input: 'input[name="give_fee_mode_checkbox"]',
				} );

				// Setup activecampaign opt-in event listeners
				setupCheckbox( {
					container: '.give-activecampaign-fieldset',
					label: '.give-activecampaign-optin-label',
					input: '.give-activecampaign-optin-input',
				} );

				// Setup mailchimp opt-in event listeners
				setupCheckbox( {
					container: '.give-mailchimp-fieldset',
					label: '.give-mc-message-text',
					input: 'input[name="give_mailchimp_signup"]',
				} );

				// Setup constant contact opt-in event listeners
				setupCheckbox( {
					container: '.give-constant-contact-fieldset',
					label: '.give-constant-contact-fieldset span',
					input: 'input[name="give_constant_contact_signup"]',
				} );

				// Setup terms and conditions opt-in event listeners
				setupCheckbox( {
					container: '#give_terms_agreement',
					label: '#give_terms_agreement label',
					input: 'input[name="give_agree_to_terms"]',
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

				// Setup gateway icons
				setupGatewayIcons();

				// Setup Form Field Manager input listeners
				setupFFMInputs();

				// Setup Icons for inputs
				setupInputIcons();

				const observer = new window.MutationObserver( function( mutations ) {
					mutations.forEach( function( mutation ) {
						if ( ! mutation.addedNodes ) {
							return;
						}

						for ( let i = 0; i < mutation.addedNodes.length; i++ ) {
							// do things to your newly added nodes here
							const node = mutation.addedNodes[ i ];

							if ( $( node ).find( '.give_error' ).length > 0 ) {
								moveErrorNotice( $( node ).find( '.give_error' ) );
								scrollToIframeTop();
							}

							if ( $( node ).children().hasClass( 'give_errors' ) ) {
								if ( ! $( node ).parent().hasClass( 'donation-errors' ) ) {
									$( node ).children( '.give_errors' ).each( function() {
										const notice = $( this );
										moveErrorNotice( notice );
									} );
								}
								scrollToIframeTop();
							}

							if ( $( node ).hasClass( 'give_errors' ) && ! $( node ).parent().hasClass( 'donation-errors' ) ) {
								moveErrorNotice( $( node ) );
								$( '.sequoia-loader' ).removeClass( 'spinning' );
								scrollToIframeTop();
							}

							if ( $( node ).attr( 'id' ) === 'give_tributes_address_state' ) {
								const placeholder = $( node ).attr( 'placeholder' );
								$( node ).prepend( `<option selected disabled>${ placeholder }</option>` );
							}

							if ( $( node ).attr( 'name' ) === 'give_tributes_address_state' && $( node ).attr( 'class' ).includes( 'give-input' ) ) {
								$( node ).attr( 'placeholder', $( node ).siblings( 'label' ).text().trim() );
							}

							if ( $( node ).attr( 'id' ) && $( node ).attr( 'id' ).includes( 'give-checkout-login-register' ) ) {
								setupRegistrationFormInputFields();
							}

							if ( $( node ).prop( 'tagName' ) && $( node ).prop( 'tagName' ).toLowerCase() === 'select' ) {
								const placeholder = $( node ).attr( 'placeholder' );
								if ( placeholder ) {
									$( node ).prepend( `<option value="" disabled selected>${ placeholder }</option>` );
								}
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
	if ( $paymentGatewayContainer.length && $paymentGatewayContainer.css( 'display' ) !== 'none' ) {
		// Move payment information section when document load.
		moveFieldsUnderPaymentGateway();

		// Move payment information section when gateway updated.
		$( document ).on( 'give_gateway_loaded', function() {
			setTimeout( setupTabOrder, 200 );

			moveFieldsUnderPaymentGateway();
			setupRegistrationFormInputFields();
			setupFFMInputs();
			setupInputIcons();
			setupSelectInputs();

			$( '#give_purchase_form_wrap' ).slideDown( 200, function() {
				gatewayAnimating = false;
			} );
		} );
		$( document ).on( 'Give:onPreGatewayLoad', function() {
			gatewayAnimating = true;
			$( '#give_purchase_form_wrap' ).slideUp( 200 );
		} );

		// Clear gateway related errors
		$( document ).on( 'Give:onPreGatewayLoad', function() {
			const persistedNotices = [
				'give_error_test_mode',
			];

			$( '.give_errors, .give_notices, .give_error' ).each( function() {
				if ( ! persistedNotices.includes( $( this ).attr( 'id' ) ) ) {
					$( this ).slideUp( 200, function() {
						$( this ).remove();
					} );
				}
			} );
		} );
	} else {
		$( '#give_purchase_form_wrap' ).addClass( 'give-single-gateway-wrap' );
	}

	// Refresh personal information section.
	$( document ).on( 'give_gateway_loaded', refreshPersonalInformationSection );

	// Setup fields.
	setupSelectInputs();
	setupRegistrationFormInputFields();
	setupFFMInputs();
	setupInputIcons();

	if ( 'enabled' === templateOptions.payment_amount.decimals_enabled ) {
		updateFormDonationLevelsLabels();
	}

	/**
	 * Limited scope of optional input labels, specifically to User Info, see issue #5160.
	 */
	setupOptionalInputLables(
		Array.from( document.querySelectorAll( '#give_checkout_user_info input[type="text"]' ) )
	);

	/**
	 * Denote non-required fields as optional.
	 *
	 * @since 2.8.0
	 *
	 * @param {array} inputs An iteratable list of input elements.
	 */
	function setupOptionalInputLables( inputs ) {
		inputs.filter( function( input ) {
			return ! input.required;
		} ).map( function( input ) {
			input.placeholder += templateL10n.optionalLabel;
		} );
	}

	/**
	 * Move error notices to error notice container at the top of the payment section
	 * @since 2.7.0
	 * @since 2.12.0 Prevents a race condition that caused an error message not to be visible.
	 * @param {node} node The error notice node to be moved
	 */
	function moveErrorNotice( node ) {
		//If the error is inside stripe payment button, do nothing
		if ( $( node ).parent().hasClass( 'give-stripe-payment-request-button' ) ) {
			return;
		}

		// First check if specific donation notice container has been set up
		if ( $( '.donation-errors' ).length === 0 ) {
			$( '.payment' ).prepend( '<div class="donation-errors"></div>' );
		}

		/**
		 * @link https://github.com/impress-org/givewp/issues/5867
		 *
		 * This 1 millisecond timeout prevents a race condition between
		 * the `.donation-errors` element being appended to the form
		 * and relocation of the specific error message node.
		 */
		setTimeout(function(){
			// If a specific notice does not already exist, proceed with moving the error
			if ( typeof $( '.donation-errors' ).html() !== undefined && ! $( '.donation-errors' ).html().includes( $( node ).html() ) ) {
				$( node ).appendTo( '.donation-errors' );
			} else {
				// If the specific notice already exists, do not add it
				$( node ).remove();
			}
		}, 1);
	}

	/**
	 * Setup registration form input fields.
	 * @since 2.9.6
	 */
	function setupRegistrationFormInputFields() {
		const handleInput = function( evt ) {
			if ( ! $( evt.target ).is( 'input' ) ) {
				return;
			}

			if ( $( evt.target ).hasClass( 'give-disabled' ) ) {
				return;
			}

			// Registration account fields only contains checkboxes.
			$( evt.target ).closest( 'label' ).toggleClass( 'checked' );
		};

		$( '[id*="give-register-account-fields"]' )
			.off( 'click', handleInput )
			.on( 'click', handleInput );
	}

	/**
	 * Add listeners and starting states to FFM inputs
	 * @since 2.7.0
	 */
	function setupFFMInputs() {
		$( '#give-ffm-section' )
			.off( 'click', handleFFMInput )
			.on( 'click', handleFFMInput );

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
	}

	/**
	 * Move form field under payment gateway
	 * @since 2.7.0
	 */
	function moveFieldsUnderPaymentGateway() {
		// Check if donate fieldset area has been created, if not set it up below payment gateways
		// This area is necessary for correctly placing various elements (fee recovery notice, newsletters, submit button, etc)
		if ( $( '#donate-fieldset' ).length === 0 ) {
			$( '.give-section.payment' ).append( '<fieldset id="donate-fieldset"></fieldset>' );
		}

		// Elements to move into donate fieldset (located at bottom of form)
		// The elements will appear in order of array
		const donateFieldsetElements = [
			'.give-constant-contact-fieldset',
			'.give-activecampaign-fieldset',
			'.give-mailchimp-fieldset',
			'#give_terms_agreement',
			'.give-donation-submit',
		];

		// Handle moving elements into donate fieldset
		donateFieldsetElements.forEach( function( selector ) {
			if ( $( `#donate-fieldset  ${ selector }` ).length === 0 ) {
				$( `#donate-fieldset  ${ selector }` ).remove();
				$( '#donate-fieldset' ).append( $( `#give_purchase_form_wrap ${ selector }` ) );
			} else if ( $( `#donate-fieldset  ${ selector }` ).html() !== $( `#give_purchase_form_wrap  ${ selector }` ).html() && $( `#give_purchase_form_wrap  ${ selector }` ).html() !== undefined ) {
				$( `#donate-fieldset  ${ selector }` ).remove();
				$( '#donate-fieldset' ).append( $( `#give_purchase_form_wrap ${ selector }` ) );
			} else {
				$( `#give_purchase_form_wrap ${ selector }` ).remove();
			}
		} );

		// Move purchase fields (credit card, billing, etc)
		$( 'li.give-gateway-option-selected' ).after( $( '#give_purchase_form_wrap' ) );

		// Add gateway class to fields wrapper, indicating which gateway is active
		const gatewayClass = 'gateway-' + $( '.give-gateway-option-selected input' ).attr( 'value' ).replace( '_', '-' );
		$( '#give_purchase_form_wrap' ).attr( 'class', gatewayClass );
	}

	/**
	 * Refresh personal information section
	 *
	 * @since 2.7.0
	 * @since 2.10.2 Rename function
	 * @param {boolean} ev Event object
	 * @param {object} response Response object
	 * @param {number} formID Form ID
	 */
	function refreshPersonalInformationSection( ev, response, formID ) {
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
				const container = $form.find( '[id^=give-checkout-login-register]' );

				// Remove existing personal information field if response contains new fields.
				if ( container.length && postResponse.fields.includes( 'give_checkout_user_info' ) ) {
					$form.find( '#give_checkout_user_info' ).remove();
				}

				container.replaceWith( $.parseJSON( postResponse.fields ) );
				container.css( { display: 'block' } );

				$form.find( '.give-submit-button-wrap' ).show();
			} ).done( function() {
				// Trigger float-labels
				window.give_fl_trigger();

				setupInputIcons();
			} );
		}
	}

	function setupInputIcon( selector, icon ) {
		$( selector ).each( function() {
			if ( $( this ).html() !== '' && $( this ).html().includes( `<i class="fas fa-${ icon }"></i>` ) === false ) {
				const property = isRTL() ? 'padding-right' : 'padding-left';
				$( this ).prepend( `<i class="fas fa-${ icon }"></i>` );
				$( this ).children( 'input, selector' ).each( function() {
					$( this ).attr( 'style', property + ': 33px!important;' );
				} );
			}
		} );
	}

	function setupInputIcons() {
		setupInputIcon( '#give-first-name-wrap', 'user' );
		setupInputIcon( '#give-email-wrap', 'envelope' );
		setupInputIcon( '#give-company-wrap', 'building' );
		setupInputIcon( '#date_field-wrap', 'calendar-alt' );
		setupInputIcon( '#url_field-wrap', 'globe' );
		setupInputIcon( '#phone_field-wrap', 'phone' );
		setupInputIcon( '#give-phone-wrap', 'phone' );
		setupInputIcon( '#email_field-wrap', 'envelope' );
	}

	/**
	 * Setup tab order for elements in form
	 *
	 * @since 2.7.0
	 */
	function setupTabOrder() {
		$( 'select, button, input, textarea, multiselect, a' ).attr( 'tabindex', -1 );

		const tabOrder = steps[ navigator.currentStep ].tabOrder;
		tabOrder.forEach( ( selector, index ) => {
			$( selector ).attr( 'tabindex', index + 1 );
		} );
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
				case 'paypal-commerce':
					icon = 'far fa-credit-card';
					break;
				case 'stripe_google_pay':
					icon = 'fab fa-google';
					break;
				case 'stripe_apple_pay':
					icon = 'fab fa-apple';
					break;
				default:
					icon = 'fas fa-hand-holding-heart';
					break;
			}
			$( this ).append( `<i class="${ icon }"></i>` );
		} );
	}

	/**
	 * Setup prominent checkboxes (that use persistent borders on select)
	 *
	 * @since 2.7.0
	 * @param {object} args Argument object containing: container, label, input selectors
	 */
	function setupCheckbox( { container, label, input } ) {
		// If checkbox is opted in by default, add border on load
		if ( $( input ).prop( 'checked' ) === true ) {
			$( container ).addClass( 'active' );
		}

		// Persist checkbox input border when selected
		$( document ).on( 'click', label, function( evt ) {
			if ( container === label ) {
				evt.stopPropagation();
				evt.preventDefault();

				$( input ).prop( 'checked', ! $( input ).prop( 'checked' ) );
			}

			$( container ).toggleClass( 'active' );
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
					$( evt.target ).closest( 'label' ).toggleClass( 'checked' );
					break;
				}
				case 'radio': {
					$( evt.target ).closest( 'label' ).addClass( 'selected' );
					$( evt.target ).parent().siblings().removeClass( 'selected' );
					break;
				}
			}
		}
	}

	function clearLoginNotices() {
		$( '#give_error_must_log_in' ).remove();
	}

	/**
	 * Setup select inputs
	 *
	 * @since 2.7.0
	 */
	function setupSelectInputs() {
		if ( $( 'select option[selected="selected"][value=""]' ).length > 0 ) {
			$( 'select option[selected="selected"][value=""]' ).each( function() {
				if ( $( this ).parent().siblings( 'label' ).length ) {
					$( this ).text( $( this ).parent().siblings( 'label' ).text().replace( '*', '' ).trim() );
					$( this ).attr( 'disabled', true );
				}
			} );
		}
	}

	/**
	 * Check if document is RTL
	 *
	 * @since 2.8.0
	 * @return {boolean} Whether or not document is RTL
	 */
	function isRTL() {
		return $( 'html' ).attr( 'dir' ) === 'rtl';
	}

	/**
	 * Scroll to parent window to iframe top
	 *
	 * @since 2.9.0
	 */
	function scrollToIframeTop() {
		if ( 'parentIFrame' in window ) {
			window.parentIFrame.sendMessage( { action: 'giveScrollIframeInToView' } );
		}
	}

	/**
	 * Update decimal donation levels amount
	 *
	 * @since 2.11.0
	 */
	function updateFormDonationLevelsLabels() {
		$( '.give-form' ).each( ( i, form ) => {
			const donationForm = $( form );
			const donationLevels = Give.form.fn.getVariablePrices( donationForm );
			const symbol = Give.form.fn.getInfo( 'currency_symbol', donationForm );
			const position = Give.form.fn.getInfo( 'currency_position', donationForm );
			const precision = Give.form.fn.getInfo( 'number_decimals', donationForm );

			$.each( donationLevels, function( j, level ) {
				if ( 'custom' === level.price_id ) {
					return;
				}

				const amount = Give.fn.numberHasDecimal( level.amount )
					? Give.fn.formatCurrency( level.amount, { symbol, position, precision }, donationForm )
					: level.amount;

				const donationLevelLabel = '<div class="currency currency--' + position + '">' + symbol + '</div>' + amount;

				donationForm
					.find( '.give-btn-level-' + level.price_id  )
					.html( donationLevelLabel );
			} );
		} );
	}
}( jQuery ) );
