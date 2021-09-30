export default {
	fn: {

		/**
		 * Open a new window prompting user to Tweet
		 * The tweet is pre-populated with supplied text, and url to share
		 *
		 * @since 2.7.0
		 */
		twitter: function twitter( url, text ) {
			const targetWindow = parent.window ? parent.window : window;
			// Calculate new window position, based on parent window height/width
			const top = targetWindow.innerHeight / 2 - 126;
			const left = targetWindow.innerWidth / 2 - 280;
			// Open new window with prompt for Twitter sharing
			targetWindow.open( `https://twitter.com/intent/tweet?url=${ encodeURIComponent( url ) }&text=${ encodeURIComponent( text ) }`, 'newwindow', `width=560,height=253,top=${ top },left=${ left }` );
		},

		/**
		 * Open a new window prompting user to share on Facebook
		 * The post is pre-populated with supplied url
		 *
		 * @since 2.7.0
		 */
		facebook: function facebook( url ) {
			const targetWindow = parent.window ? parent.window : window;
			// Calculate new window position, based on parent window height/width
			const top = targetWindow.innerHeight / 2 - 365;
			const left = targetWindow.innerWidth / 2 - 280;
			// Open new window with prompt for Facebook sharing
			window.open( `https://www.facebook.com/sharer/sharer.php?u=${ encodeURIComponent( url ) }`, 'newwindow', `width=560,height=730,top=${ top },left=${ left }` );
		},
	},
};
