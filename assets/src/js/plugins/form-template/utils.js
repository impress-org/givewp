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
				switch ( messageData.message.action ) {
					case 'giveEmbedFormContentLoaded':
						const timer = setTimeout( function() {
							revealIframe();
						}, 400 );

						let parent = iframe.parentElement;
						const iframeToAutoScroll = document.querySelector( 'iframe[name="give-embed-form"][data-autoscroll="1"]:not(.in-modal)' );
						if ( iframe.parentElement.classList.contains( 'modal-content' ) ) {
							parent = parent.parentElement.parentElement;
						}

						parent.classList.remove( 'give-loader-type-img' );
						iframe.style.visibility = 'visible';

						// Attribute to dom when iframe loaded.
						iframe.setAttribute( 'data-contentLoaded', '1' );

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
					case 'setProcessingHeight':
						iframe.style.minHeight = `${ messageData.message.payload }px`;
						break;
				}
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
