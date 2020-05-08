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
			checkOrigin: [ window.location.origin ],
			heightCalculationMethod: 'documentElementOffset',
			widthCalculationMethod: 'documentElementOffset',
			onMessage: function( messageData ) {
				let parent = iframe.parentElement;
				if ( iframe.parentElement.classList.contains( 'modal-content' ) ) {
					parent = parent.parentElement.parentElement;
				}

				switch ( messageData.message.action ) {
					case 'giveEmbedFormContentLoaded':

						const timer = setTimeout( function() {
							revealIframe();
						}, 400 );

						// Attribute to dom when iframe loaded.
						iframe.setAttribute( 'data-contentLoaded', '1' );

						function revealIframe() {
							clearTimeout( timer );
							parent.querySelector( '.iframe-loader' ).style.opacity = 0;
							parent.querySelector( '.iframe-loader' ).style.transition = 'opacity 0.2s ease';
							iframe.style.visibility = 'visible';
							iframe.style.minHeight = '';
						}
						break;
					case 'showLoader':
						parent.querySelector( '.iframe-loader' ).style.opacity = 1;
						parent.querySelector( '.iframe-loader' ).style.transition = '';
						iframe.style.visibility = 'hidden';
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
