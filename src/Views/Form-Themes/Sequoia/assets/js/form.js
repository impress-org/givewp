/* globals jQuery, Give */
( function( $ ) {
	$( '.give-show-form button', '.give-embed-form' ).on( 'click', function( e ) {
		e.preventDefault();
		const $parent = $( this ).parent(),
			  $container = $( '.give-embed-form' ),
			  $form = $( 'form', $container );

		if ( $parent.hasClass( 'give-showing__introduction-section' ) ) {
			// Hide introduction section.
			$( '> *:not(.give_error):not(form):not(.give-show-form)', $container ).hide();

			// Show choose amount section
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
			$.post( Give.fn.getGlobalVar( 'ajaxurl' ), data, function( response ) {
				$form.find( '[id^=give-checkout-login-register]' ).replaceWith( $.parseJSON( response.fields ) );
				$form.find( '[id^=give-checkout-login-register]' ).css( { display: 'block' } );
				$form.find( '.give-submit-button-wrap' ).show();
			} ).done( function() {
				// Trigger float-labels
				give_fl_trigger();
			} );
		}
	}
}( jQuery ) );
