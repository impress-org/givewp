/* globals jQuery, Give */
( function( $ ) {
	const templateOptions = window.sequoiaTemplateOptions;
	const $container = $( '.give-embed-form' );
	const $advanceButton = $( '.advance-btn', $container );
	const $backButton = $( '.back-btn' );
	const $navigatorTitle = $( '.give-form-navigator .title' );

	const navigator = {
		currentStep: templateOptions.introduction.enabled === 'enabled' ? 0 : 1,
		animating: false,
		goToStep: ( step ) => {
			if ( steps[ step ].showErrors === true ) {
				$( '.give_error, .give_warning, .give_success', '.give-form-wrap' ).show();
			} else {
				$( '.give_error, .give_warning, .give_success', '.give-form-wrap' ).hide();
			}

			$( '.step-tracker' ).removeClass( 'current' );
			$( '.step-tracker[data-step="' + step + '"]' ).addClass( 'current' );

			if ( templateOptions.introduction.enabled === 'disabled' ) {
				if ( $( '.step-tracker' ).length === 3 ) {
					$( '.step-tracker:first-of-type' ).remove();
				}
				step = step > 0 ? step : 1;
				if ( step === 1 ) {
					$( '.back-btn', $container ).hide();
				} else {
					$( '.back-btn', $container ).show();
				}
			} else if ( step === 0 ) {
				$( '.give-form-navigator', $container ).hide();
			} else {
				$( '.give-form-navigator', $container ).show();
			}

			$navigatorTitle.text( steps[ step ].title );

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
			} else {
				$( steps[ navigator.currentStep ].selector ).css( 'position', 'absolute' );
			}
			navigator.currentStep = step;
		},
		init: () => {
			steps.forEach( ( step ) => {
				if ( step.setup !== undefined ) {
					step.setup();
				}
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
			setupHeightChangeCallback( function( height, diff ) {
				if ( diff > 10 ) {
					$( '.form-footer' ).css( 'transition', 'margin-top 0.2s ease' );
				} else {
					$( '.form-footer' ).css( 'transition', '' );
				}
				$( '.form-footer' ).css( 'margin-top', `${ height }px` );
			} );
			navigator.goToStep( 0 );
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
				$( '.give-donation-level-btn' ).each( function() {
					const hasTooltip = $( this ).attr( 'has-tooltip' );
					if ( hasTooltip ) {
						return;
					}

					const value = $( this ).attr( 'value' );
					const text = $( this ).text();
					if ( value !== 'custom' ) {
						const wrap = `<span class="give-tooltip hint--top hint--bounce" style="width: 100%" aria-label="${ text }" rel="tooltip"></span>`;
						const symbol = $( '.give-currency-symbol' ).text();
						const position = $( '.give-currency-symbol' ).hasClass( 'give-currency-position-before' ) ? 'before' : 'after';
						const html = position === 'before' ? `<div class="currency">${ symbol }</div>${ value }` : `${ value }<div class="currency">${ symbol }</div>`;
						$( this ).html( html );
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

				//Override submit loader with Sequoia loader
				window.give_global_vars.purchase_loading = '';
				$( '.give-loading-animation' ).removeClass( 'give-loading-animation' ).addClass( 'sequoia-loader' );

				// Show Sequoia loader on click/touchend
				$( 'body' ).on( 'click touchend', 'form.give-form input[name="give-purchase"].give-submit', function() {
					$( 'form.give-form input[name="give-purchase"].give-submit' ).css( 'color', templateOptions.introduction.primary_color );
					$( '.sequoia-loader' ).addClass( 'spinning' );
				} );

				//Setup input icons
				setupInputIcon( '#give-first-name-wrap', 'user' );
				setupInputIcon( '#give-email-wrap', 'envelope' );
			},
		},
	];

	navigator.init();

	// Move payment information section when document load.
	moveFieldsUnderPaymentGateway( true );

	// Move payment information section when gateway updated.
	$( document ).on( 'give_gateway_loaded', function() {
		moveFieldsUnderPaymentGateway( true );
	} );
	$( document ).on( 'Give:onPreGatewayLoad', function() {
		moveFieldsUnderPaymentGateway( false );
	} );

	// Refresh payment information section.
	$( document ).on( 'give_gateway_loaded', refreshPaymentInformationSection );

	/**
	 * Move form field under payment gateway
	 * @since 2.7.0
	 * @param {boolean} $refresh Flag to remove or add form fields to selected payment gateway.
	 */
	function moveFieldsUnderPaymentGateway( $refresh = false ) {
		// This function will run only for embed donation form.
		if ( 1 !== parseInt( jQuery( 'div.give-embed-form' ).length ) ) {
			return;
		}

		if ( ! $refresh ) {
			const element = jQuery( 'li.give_purchase_form_wrap-clone' );
			element.slideUp( 300, function() {
				element.remove();
			} );

			return;
		}

		new Promise( function( res ) {
			const fields = jQuery( '#give_purchase_form_wrap > *' ).not( '.give-donation-submit' );
			let showFields = false;

			jQuery( '.give-gateway-option-selected' ).after( '<li class="give_purchase_form_wrap-clone" style="display: none"></li>' );

			jQuery.each( fields, function( index, $item ) {
				$item = jQuery( $item );
				jQuery( '.give_purchase_form_wrap-clone' ).append( $item.clone() );

				showFields = ! showFields ? !! $item.html().trim() : showFields;

				$item.remove();
			} );

			if ( ! showFields ) {
				jQuery( '.give_purchase_form_wrap-clone' ).remove();
			}

			return res( showFields );
		} ).then( function( showFields ) {
			// eslint-disable-next-line no-unused-expressions
			setupInputIcon( '#give-card-country-wrap', 'globe-americas' );

			// eslint-disable-next-line no-unused-expressions
			showFields && jQuery( '.give_purchase_form_wrap-clone' ).slideDown( 300, function() {
				const height = $( '.payment' ).height();
				$( '.form-footer' ).css( 'margin-top', `${ height }px` );
			} );
		} );
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

	function setupHeightChangeCallback( callback ) {
		let lastHeight = 0;
		function checkHeightChange() {
			const selector = $( steps[ navigator.currentStep ].selector );
			const changed = lastHeight !== $( selector ).height();
			if ( changed ) {
				const diff = lastHeight > $( selector ).height() ? lastHeight - $( selector ).height() : $( selector ).height() - lastHeight;
				callback( $( selector ).height(), diff );
				lastHeight = $( selector ).height();
			}
			window.requestAnimationFrame( checkHeightChange );
		}
		window.requestAnimationFrame( checkHeightChange );
	}
}( jQuery ) );
