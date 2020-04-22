// this script is only for parent page
const iFrameResizer = {
	targetOrigin: window.location.origin,

	onReady: function() {
		if ( ! document.getElementById( 'give-receipt' ) ) {
			window.parentIFrame.sendMessage( { action: 'giveEmbedFormContentLoaded' } );
		}

		window.addEventListener( 'beforeunload', function() {
			const height = document.querySelector( '.give-form-templates' ).offsetHeight;
			const message = {
				action: 'showLoader',
				payload: height,
			};
			window.parentIFrame.sendMessage( message );
		} );
	},

	onMessage: function( message ) {
		if ( 'currentPage' in message ) {
			const $field = document.getElementsByName( 'give-current-url' );
			if ( $field.length ) {
				$field[ 0 ].setAttribute( 'value', message.currentPage );
			}
		}
	},
};

export default iFrameResizer;
