/* globals jQuery */
( function( $ ) {
	$( '.give-show-form button', '.give-embed-form' ).on( 'click', function( e ) {
		e.preventDefault();
		const $container = $( '.give-embed-form' ),
			  $form = $( 'form', $container ),
			  $paymentGateways = $( '[id="give-payment-mode-wrap"] li:not(.give_purchase_form_wrap-clone)', $form );

		// Add flex class in case of more then one payment gateway active.
		if ( 1 < parseInt( $paymentGateways.length ) ) {
			$.each( $paymentGateways, function( index, $item ) {
				$( $item ).addClass( 'give-flex' );
			} );
		}

		$( '> *:not(.give_error):not(form)', $container ).hide();

		$( '.give-donation-levels-wrap', $form ).addClass( 'give-grid' );
		$( '.give-total-wrap', $form ).addClass( 'give-flex' );
		$form.slideDown();

		// Hide payment gateway option in case on one gateway is active.
		if ( 1 === parseInt( $paymentGateways.length ) ) {
			$paymentGateways.hide();
		}

		if ( 'parentIFrame' in window ) {
			window.parentIFrame.sendMessage( 'giveEmbedShowingForm' );
		}
	} );

	// Move personal information section when document load.
	giveMoveFieldsUnderPaymentGateway( true );

	// Move personal information section when gateway updated.
	$( document ).on( 'give_gateway_loaded', function() {
		giveMoveFieldsUnderPaymentGateway( true );
	} );
	$( document ).on( 'Give:onPreGatewayLoad', function() {
		giveMoveFieldsUnderPaymentGateway( false );
	} );

	/**
	 * Move form field under payment gateway
	 *
	 * @todo: refactor this code
	 *
	 * @param {boolean} $refresh Flag to remove or add form fields to selected payment gateway.
	 */
	function giveMoveFieldsUnderPaymentGateway( $refresh = false ) {
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
}( jQuery ) );
