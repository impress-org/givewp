/* globals jQuery, Give */
( function( $ ) {
	const templateOptions = window.sequoiaTemplateOptions;
	const $container = $( '.give-embed-form' );
	const $advanceButton = $( '.give-show-form button', $container );
	const $backButton = $( '.back-btn' );
	const $navigatorTitle = $( '.give-form-navigator .title' );

	const navigator = {
		currentStep: null,
		animating: false,
		goToStep: ( step ) => {
			$advanceButton.text( steps[ step ].label );
			$navigatorTitle.text( steps[ step ].title );

			const hide = steps.map( ( obj, index ) => {
				if ( index !== step ) {
					return obj.selector;
				}
			} );
			const hideSelector = hide.filter( Boolean ).join( ', ' );

			$( hideSelector ).hide();
			$( steps[ step ].selector ).show();

			if ( step === steps.length - 1 ) {
				$advanceButton.hide();
			} else {
				$advanceButton.show();
			}

			steps[ step ].setup();
			navigator.currentStep = step;
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
			title: 'Introduction',
			selector: '.give-section.introduction, .give-section.income-stats, .give-section.progress-bar',
			label: templateOptions.introduction.donate_label,
			setup: () => {
				$( '.give-form-navigator', $container ).hide();
			},
		},
		{
			id: 'choose-amount',
			title: 'Choose Amount',
			selector: '.give-section.choose-amount',
			label: templateOptions.payment_amount.next_label,
			setup: () => {
				$( '.give-form-navigator', $container ).show();

				$( '.give-donation-level-btn' ).each( function() {
					const value = $( this ).attr( 'value' );
					const text = $( this ).text();
					if ( value !== 'custom' ) {
						const wrap = `<span class="give-tooltip hint--top hint--bounce" style="width: 100%" aria-label="${ text }" rel="tooltip"></span>`;
						const html = `<div class="currency">$</div>${ value }`;
						$( this ).html( html );
						$( this ).wrap( wrap );
					}
				} );
				$( '.give-total-wrap', $container ).addClass( 'give-flex' );
				$( '.give-donation-levels-wrap', $container ).addClass( 'give-grid' );
			},
		},
		{
			id: 'personal',
			title: 'Add Your Information',
			label: 'Process Donation',
			selector: '.give-section.personal, #give_checkout_user_info, #give-payment-mode-select, #give_purchase_form_wrap',
			setup: () => {
				// Show remain form options.
				$( '.give-label' ).html( '' );
				$( 'label[for=give-first]' ).html( '<i class="fas fa-user"></i>' );
				$( 'label[for=give-email]' ).html( '<i class="fas fa-envelope"></i>' );
			},
		},
	];

	const styles = {
		setup: () => {
			// Setup custom styles stylesheet
			const sheet = ( function() {
				// Create the <style> tag
				const style = document.createElement( 'style' );

				// WebKit hack :(
				style.appendChild( document.createTextNode( '' ) );

				// Add the <style> element to the page
				document.head.appendChild( style );

				return style.sheet;
			}() );

			const primaryColor = templateOptions.introduction.primary_color;

			// Insert rules to custom stylesheet
			sheet.insertRule( `.seperator {
				background: ${ primaryColor }!important;
			}` );
			sheet.insertRule( `.give-btn {
				background: ${ primaryColor }!important;
				transition: box-shadow 0.2s ease;
				box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0),
					0 3px 1px -2px rgba(0, 0, 0, 0),
					0 1px 5px 0 rgba(0, 0, 0, 0)
					!important;
				border: 2px solid ${ primaryColor }!important;
			}` );
			sheet.insertRule( `.give-btn:hover {
				background: ${ primaryColor }!important;
				box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14),
					0 3px 1px -2px rgba(0, 0, 0, 0.2),
					0 1px 5px 0 rgba(0, 0, 0, 0.12)
					!important;
			}` );
			sheet.insertRule( `.give-donation-level-btn {
				border: 2px solid ${ primaryColor }!important;
			}` );
			sheet.insertRule( `.give-donation-level-btn.give-default-level {
				color: ${ primaryColor }!important; background: #fff!important;
				transition: background 0.2s ease, color 0.2s ease;
			}` );
			sheet.insertRule( `.give-donation-level-btn.give-default-level:hover {
				color: ${ primaryColor }!important; background: #fff!important;
			}` );
		},
	};

	styles.setup();
	navigator.goToStep( 0 );
	$advanceButton.on( 'click', function( e ) {
		e.preventDefault();
		navigator.forward();
		if ( 'parentIFrame' in window ) {
			window.parentIFrame.sendMessage( 'giveEmbedShowingForm' );
		}
	} );
	$backButton.on( 'click', function( e ) {
		e.preventDefault();
		navigator.back();
	} );

	// Move personal information section when document load.
	moveFieldsUnderPaymentGateway( true );

	// Move personal information section when gateway updated.
	$( document ).on( 'give_gateway_loaded', function() {
		moveFieldsUnderPaymentGateway( true );
	} );
	$( document ).on( 'Give:onPreGatewayLoad', function() {
		moveFieldsUnderPaymentGateway( false );
	} );

	// Refresh personal information section.
	$( document ).on( 'give_gateway_loaded', refreshPersonalInformationSection );

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
			element.slideUp( 'slow', function() {
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
			showFields && jQuery( '.give_purchase_form_wrap-clone' ).slideDown( 'slow' );
		} );
	}

	/**
	 * Refresh personal information section
	 *
	 * @since 2.7.0
	 * @param {boolean} ev Event object
	 * @param {object} response Response object
	 * @param {number} formID Form ID
	 */
	function refreshPersonalInformationSection( ev, response, formID ) {
		const $form = $( `#${ formID }` );

		// This function will run only for embed donation form.
		// Show personal information section fields.
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
}( jQuery ) );
