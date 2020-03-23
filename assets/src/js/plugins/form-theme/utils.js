/*globals jQuery*/

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
				const iframe = messageData.iframe;

				switch ( messageData.message ) {
					case 'giveEmbedFormContentLoaded':
						iframe.parentElement.classList.remove( 'give-loader-type-img' );
						iframe.style.visibility = 'visible';

						break;
				}
			},
			onScroll: ( { x, y } ) => {
				jQuery( 'html, body' ).animate( { scrollTop: y, scrollLeft: x } );

				return false;
			},
			onInit: function( iframe ) {
				iframe.iFrameResizer.sendMessage( {
					currentPage: Give.fn.removeURLParameter( window.location.href, 'giveDonationAction' ),
				} );
			},
		},
		iframe
	);
};
