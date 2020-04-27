/* globals CustomEvent */
import { initializeIframeResize } from './utils';

jQuery( function( $ ) {
	// This script is only for parent page.
	if ( document.querySelector( 'body.give-form-templates' ) ) {
		return false;
	}

	/**
	 * Show hide iframe modal.
	 *
	 * @since 2.7.0
	 */
	document.querySelectorAll( '.js-give-embed-form-modal-opener' ).forEach( function( button ) {
		button.addEventListener( 'click', function() {
			const iframeContainer = document.getElementById( button.getAttribute( 'data-form-id' ) ),
				  iframe = iframeContainer.querySelector( 'iframe[name="give-embed-form"]' ),
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
		button.addEventListener( 'click', function( evt ) {
			evt.preventDefault();

			const iframeContainer = document.getElementById( button.getAttribute( 'data-form-id' ) );

			document.documentElement.style.overflow = '';

			iframeContainer.classList.remove( 'modal' );
			iframeContainer.classList.add( 'is-hide' );
		} );
	} );

	window.addEventListener( 'load', function() {
		/**
		 * Automatically open form if it is in modal.
		 */
		const $iframe = document.querySelector( '.modal-content iframe[data-autoScroll="1"]' );
		if ( $iframe ) {
			const containerID = $iframe.parentElement.parentElement.parentElement.parentElement.getAttribute( 'id' ),
				  $button = document.querySelector( `.js-give-embed-form-modal-opener[data-form-id="${ containerID }"]` );

			if ( $button ) {
				$button.click();
			}
		}
	} );
} );
