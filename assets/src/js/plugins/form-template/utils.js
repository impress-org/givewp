/*globals Give, jQuery*/

import { iframeResize } from 'iframe-resizer';

/**
 * Initialize iframe resizer on iframe.
 *
 * @since 2.7.0
 * @param {object} iframe Iframe object.
 *
 * @return {object} iframeResize object.
 */
export const initializeIframeResize = function( iframe ) {
	return new iframeResize(
		{
			log: false,
			sizeWidth: true,
			heightCalculationMethod: 'documentElementOffset',
			widthCalculationMethod: 'documentElementOffset',
			onMessage: function( messageData ) {
				switch ( messageData.message ) {
					case 'giveEmbedFormContentLoaded':
						const timer = setTimeout( function() {
							revealIframe();
						}, 600 );

						function revealIframe() {
							clearTimeout( timer );
							let parent = iframe.parentElement;
							if ( iframe.parentElement.classList.contains( 'modal-content' ) ) {
								parent = parent.parentElement.parentElement;
							}
							parent.querySelector( '.iframe-loader' ).remove();
							iframe.style.visibility = 'visible';
							iframe.style.minHeight = '';
						}
						break;
				}
			},
			onScroll: ( { x, y } ) => {
				// No need to auto scroll if form loaded in modal.
				if ( iframe.parentElement.classList.contains( 'modal-content' ) ) {
					return false;
				}

				jQuery( 'html, body' ).animate( { scrollTop: y, scrollLeft: x } );

				return false;
			},
			onInit: function( ) {
				iframe.iFrameResizer.sendMessage( {
					currentPage: Give.fn.removeURLParameter( window.location.href, 'giveDonationAction' ),
				} );
			},
		},
		iframe
	);
};
