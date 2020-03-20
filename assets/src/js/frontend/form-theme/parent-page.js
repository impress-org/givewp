/* globals jQuery, Give */
import { iframeResize } from 'iframe-resizer';

jQuery( function( $ ) {
	const $allIframes = document.querySelectorAll( 'iframe[name="give-embed-form"]:not([data-src])' ),
		  iframeCount = parseInt( $allIframes.length );
	let iframeCounter = 0;

	if ( iframeCount ) {
		$allIframes.forEach( function( iframe ) {
			initializeIframeResize( iframe );
		} );
	}

	/**
	 * Auto scroll to donor's donation form
	 *
	 * @since 2.7
	 */
	document.addEventListener( 'Give.iframesLoaded', function( e ) {
		const { iframes } = e.detail.give;

		Array.from( iframes ).forEach( function( iframe ) {
			if ( '1' === iframe.getAttribute( 'data-autoScroll' ) ) {
				scrollToIframe( iframe );

				// Exit function.
				return false;
			}
		} );
	} );

	/**
	 * Show hide iframe modal.
	 *
	 * @since 2.7.0
	 */
	document.querySelectorAll( '.js-give-embed-form-modal-opener' ).forEach( function( button ) {
		button.addEventListener( 'click', function() {
			const iframeContainer = document.getElementById( button.getAttribute( 'data-form-id' ) ),
				  iframe = iframeContainer.getElementsByTagName( 'iframe' ).item( 0 ),
				  iframeURL = iframe.getAttribute( 'data-src' );

			// Load iframe.
			if ( iframeURL ) {
				iframe.setAttribute( 'src', iframeURL );
				iframe.setAttribute( 'data-src', '' );

				initializeIframeResize( iframe );
			}

			document.documentElement.style.overflow = 'hidden';

			iframeContainer.classList.add( 'modal' );
			iframeContainer.classList.remove( 'is-hide' );
		} );
	} );

	document.querySelectorAll( '.js-give-embed-form-modal-closer' ).forEach( function( button ) {
		button.addEventListener( 'click', function() {
			const iframeContainer = document.getElementById( button.getAttribute( 'data-form-id' ) );

			document.documentElement.style.overflow = '';

			iframeContainer.classList.remove( 'modal' );
			iframeContainer.classList.add( 'is-hide' );
		} );
	} );

	/**
	 * Intialize iframeresizer on iframe.
	 *
	 * @since 2.7.0
	 * @param {object} iframe
	 *
	 * @return {object}
	 */
	function initializeIframeResize( iframe ) {
		return new iframeResize(
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
								document.dispatchEvent( new CustomEvent( 'Give.iframesLoaded', { detail: { give: { iframes: $allIframes } } } ) );
							}
							break;

						case 'giveEmbedShowingForm':
							scrollToIframe( iframe );
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
	}

	/**
	 * Scroll to iframe
	 *
	 * @since 2.7
	 * @param {object} iframe
	 */
	function scrollToIframe( iframe ) {
		// Do not scroll if iframe is in modal.
		if ( iframe.classList.contains( 'in-modal' ) ) {
			return false;
		}

		$( 'html, body' ).animate( { scrollTop: iframe.offsetTop } );
	}
} );
