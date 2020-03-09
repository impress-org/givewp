const iFrameResizer = {
	targetOrigin: location.origin,

	onReady: function() {
		window.parentIFrame.sendMessage( 'giveEmbedFormContentLoaded' );
	},

	onMessage: function( message ) {
		console.log( message );

		if ( 'currentPage' in message ) {
			const $field = document.getElementsByName( 'give-current-url' );
			if ( $field.length ) {
				$field[ 0 ].setAttribute( 'value', message.currentPage );
			}
		}
	},
};

export default iFrameResizer;
