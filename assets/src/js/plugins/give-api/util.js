export default {
	fn: {
		/**
		 * Resize Iframe
		 *
		 * @param {object} $iframe Iframe javascript selector
		 */
		resizeIframe: function( $iframe ) {
			$iframe.style.height = `${ $iframe.contentWindow.document.body.scrollHeight }px`;
			$iframe.style.width = `${ $iframe.contentWindow.document.body.scrollWidth }px`;
		},
	},
};
