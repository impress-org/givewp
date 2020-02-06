/* globals jQuery */
( function( $ ) {
	if ( $.fn.iFrameResize ) {
		// Parent page.
		$( 'iframe[name="give-embed-form"]' ).iFrameResize(
			{
				log: true,
				sizeWidth: true,
				heightCalculationMethod: 'documentElementOffset',
				widthCalculationMethod: 'documentElementOffset',
				onMessage: function( messageData ) {
					switch ( messageData.message ) {
						case 'giveEmbedFormContentLoaded':
							messageData.iframe.parentElement.classList.remove( 'give-loader-type-img' );
							messageData.iframe.style.visibility = 'visible';
							break;

						case 'giveEmbedShowingForm':
							$( 'html, body' ).animate( { scrollTop: messageData.iframe.offsetTop } );
							break;
					}
				},
				onInit: function( iframe ) {
					iframe.iFrameResizer.sendMessage( {
						currentPage: Give.fn.removeURLParameter( window.location.href, 'giveDonationAction' ),
					} );
				},
			}
		);
	}
}( jQuery ) );
