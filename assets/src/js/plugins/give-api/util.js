export default {
	fn: {
		/**
		 * Resize Iframe
		 *
		 * @param {object} $iframe Iframe javascript selector
		 */
		resizeIframe: function( $iframe ) {
			$iframe.height = `${ $iframe.contentWindow.document.body.scrollHeight }px`;
		},
	},
};
