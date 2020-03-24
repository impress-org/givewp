/* globals jQuery, Give */
import { iframeResize } from 'iframe-resizer';

jQuery( function() {
	const $allIframes = document.querySelectorAll( 'iframe[name="give-embed-form"]' ),
		iframeCount = parseInt( $allIframes.length );
	let iframeCounter = 0;

	if ( iframeCount ) {
		$allIframes.forEach( function( el ) {
			new iframeResize(
				{
					log: true,
					sizeWidth: true,
					heightCalculationMethod: 'documentElementOffset',
					widthCalculationMethod: 'documentElementOffset',
					onMessage: function( messageData ) {
						const iframe = messageData.iframe;

						switch ( messageData.message ) {
							case 'giveEmbedFormContentLoaded':
								iframe.parentElement.classList.remove( 'give-loader-type-img' );
								iframe.style.visibility = 'visible';

								// Check if all iframe loaded. if yes, then trigger custom action.
								iframeCounter++;
								if ( iframeCounter === iframeCount ) {
									document.dispatchEvent( new window.CustomEvent( 'Give.iframesLoaded', { detail: { give: { iframes: $allIframes } } } ) );
								}
								break;
						}
					},
					onInit: function( iframe ) {
						iframe.iFrameResizer.sendMessage( {
							currentPage: Give.fn.removeURLParameter( window.location.href, 'giveDonationAction' ),
						} );
					},
				},
				el
			);
		} );
	}
} );
