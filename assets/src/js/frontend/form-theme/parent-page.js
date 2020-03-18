/* globals jQuery, Give */
import { iframeResize } from 'iframe-resizer';

jQuery( function( $ ) {
	const $allIframes = document.querySelectorAll( 'iframe[name="give-embed-form"]' ),
		  iframeCount = parseInt( $allIframes.length );
	let iframeCounter = 0;

	/**
	 * Scroll to iframe
	 *
	 * @since 2.7
	 * @param {object} iframe
	 */
	function scrollToIframe( iframe ) {
		$( 'html, body' ).animate( { scrollTop: iframe.offsetTop } );
	}

	if ( iframeCount ) {
		$allIframes.forEach( function( iframe ) {
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

	$.each( $( '.give-embed-form-popup-button' ), function( index, button ) {
		button = $( button );

		button.magnificPopup( {
			fixedContentPos: true,
			fixedBgPos: true,
			closeBtnInside: true,
			midClick: true,
			removalDelay: 300,
			mainClass: 'give-embed-form-modal',
		} );
	} );
} );
