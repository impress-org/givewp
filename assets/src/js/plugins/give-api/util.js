/* globals Give */
export default {
	fn: {
		/**
		 * Show processing state template.
		 *
		 * @since 2.8.0
		 * @since {string} html Message html string or plain text
		 */
		showOverlay: function( html ) {
			const container = document.createElement( 'div' );
			const loader = document.createElement( 'div' );
			const divNode = document.createElement( 'div' );

			if ( html ) {
				divNode.innerHTML = html;
			} else {
				divNode.innerHTML = Give.fn.getGlobalVar( 'textForOverlayScreen' );
			}

			loader.setAttribute( 'class', 'loader spinning' );
			container.setAttribute( 'id', 'give-processing-state-template' );

			container.append( loader );
			container.append( divNode );

			container.classList.add( 'active' );
			document.body.appendChild( container );
		},

		/**
		 * Hide processing state template.
		 *
		 * @since 2.8.0
		 */
		hideOverlay: function( ) {
			document.getElementById( 'give-processing-state-template' ).remove();
		},
	},
};
