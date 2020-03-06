/* globals Give, jQuery */
export default {
	fn: {
		/**
		 * Check if donor has session or not
		 *
		 * @since 2.3.1
		 *
		 * @param {object} $form
		 * @return {boolean}
		 */
		hasSession: function( $form ) {
			if ( ! $form.length ) {
				return false;
			}

			return !! Give.fn.__getCookie( Give.fn.getGlobalVar( 'session_cookie_name' ) );
		},

		/**
		 * Check if donor is logged with WP User
		 *
		 * @since 2.3.1
		 *
		 * @return {boolean}
		 */
		isLoggedIn: function() {
			return jQuery( 'body' ).hasClass( 'logged-in' );
		},
	},
};
