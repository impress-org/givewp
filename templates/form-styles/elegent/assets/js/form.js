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
}( jQuery ) );
