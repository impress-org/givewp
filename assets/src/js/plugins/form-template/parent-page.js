import { initializeIframeResize } from './utils';

jQuery( function() {
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

	/**
	 * Trigger click on embedded form modal launcher when click on grid item form modal launcher.
	 *
	 * Note: This code with make form template (other then legacy form template) compatible with form grid.
	 */
	document.querySelectorAll( '.js-give-grid-modal-launcher' ).forEach( function( $formModalLauncher ) {
		$formModalLauncher.addEventListener( 'click', function() {
			const $embedFormLauncher = $formModalLauncher.nextElementSibling.firstElementChild,
				$magnificPopContainer = document.querySelector( '.mfp-wrap.give-modal' );

			$magnificPopContainer && $magnificPopContainer.classList.add( 'mfp-hide' );

			// Exit if form has legacy form template.
			if ( ! $embedFormLauncher ) {
				$magnificPopContainer && $magnificPopContainer.classList.remove( 'mfp-hide' );
				return;
			}

			// Do not open magnific poppup.
			jQuery.magnificPopup.close();

			$embedFormLauncher.click();
		} );
	} );

    /*
     * Close form modal by clicking on the background
     * @since 2.19.6
     */
    document.addEventListener('click', function(e){
        if (e.target.matches('.modal-inner-wrap') || e.target.matches('.give-embed-form-wrapper.modal')) {
           e.target.querySelector('.js-give-embed-form-modal-closer').click();
        }
    });


	/**
	 * Close embed form modal when press "esc" key.
	 */
	document.addEventListener( 'keydown', event => {
		// Exit if pressed keycode is not 27. Only listen for "esc" key
		if ( event.isComposing || event.keyCode !== 27 ) {
			return;
		}

		// Close modal if open.
		const $modal = document.querySelector( '.give-embed-form-wrapper.modal' );
		if ( $modal ) {
			const containerId = $modal.getAttribute( 'id' ),
				$button = document.querySelector( `.js-give-embed-form-modal-closer[data-form-id="${ containerId }"]` );

			$button && $button.click();
		}
	} );

	window.addEventListener( 'load', function() {
		/**
		 * Automatically open form if it is in modal.
		 */
		const $iframe = document.querySelector( '.modal-content iframe[data-autoScroll="1"]' );
		if ( $iframe ) {
			const containerId = $iframe.parentElement.parentElement.parentElement.getAttribute( 'id' ),
				$button = document.querySelector( `.js-give-embed-form-modal-opener[data-form-id="${ containerId }"]` );

			if ( $button ) {
				$button.click();
			}
		}
	} );
} );
