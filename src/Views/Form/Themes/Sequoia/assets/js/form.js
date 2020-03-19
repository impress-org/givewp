/* globals jQuery, Give */
( function( $ ) {
	// Setup custom styles stylesheet
	const sheet = ( function() {
		// Create the <style> tag
		const style = document.createElement( 'style' );

		// Add a media (and/or media query) here if you'd like!
		// style.setAttribute("media", "screen")
		// style.setAttribute("media", "only screen and (max-width : 1024px)")

		// WebKit hack :(
		style.appendChild( document.createTextNode( '' ) );

		// Add the <style> element to the page
		document.head.appendChild( style );

		return style.sheet;
	}() );

	const templateOptions = window.sequoiaTemplateOptions;
	const primaryColor = templateOptions.introduction.primary_color;
	const donateLabel = templateOptions.introduction.donate_label;
	const nextLabel = templateOptions.payment_amount.next_label;

	// Insert rules to custom stylesheet
	sheet.insertRule( `.seperator { background: ${ primaryColor }!important;}` );
	sheet.insertRule( `.give-btn { background: ${ primaryColor }!important;}` );
	sheet.insertRule( `.give-donation-level-btn { border: 2px solid ${ primaryColor }!important;}` );
	sheet.insertRule( `.give-donation-level-btn.give-default-level { color: ${ primaryColor }!important; background: #fff!important;}` );

	const advanceButton = $( '.give-show-form button', '.give-embed-form' );
	$( advanceButton ).text( donateLabel );

	$( advanceButton ).on( 'click', function( e ) {
		e.preventDefault();
		const $parent = $( this ).parent(),
			$container = $( '.give-embed-form' ),
			$form = $( 'form', $container );

		if ( $parent.hasClass( 'give-showing__introduction-section' ) ) {
			// Hide introduction section.
			$( '> *:not(.give_error):not(form):not(.give-show-form)', $container ).hide();

			// Show choose amount section
			$( advanceButton ).text( nextLabel );

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
			$( '.give-section.choose-amount', $form ).show();

			$parent.removeClass( 'give-showing__introduction-section' ).addClass( 'give-showing_choose-amount-section' );
		} else if ( $parent.hasClass( 'give-showing_choose-amount-section' ) ) {
			// Hide choose amount section.
			$( '.give-total-wrap', $container ).removeClass( 'give-flex' );
			$( '.give-section.choose-amount', $form ).hide();

			// Hide paginate button.
			$( '.give-show-form', $container ).hide();

			// Show remain form options.
			$( 'form > *:not(.give-section.choose-amount)', $container ).show();
			$( '.give-label' ).html( '' );
			$( 'label[for=give-first]' ).text( 'n' );
			$( 'label[for=give-email]' ).text( 'e' );

			$parent.removeClass( 'give-showing_choose-amount-section' ).addClass( 'give-showing__personal-section' );
		}

		if ( 'parentIFrame' in window ) {
			window.parentIFrame.sendMessage( 'giveEmbedShowingForm' );
		}
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
