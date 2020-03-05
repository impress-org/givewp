// Use only in WP Backend.
jQuery( document ).ready( function( $ ) {
	$( 'body' ).on( 'click', '.notice-dismiss', function( event ) {
		const $el = $( this ).parent().parent();

		// Exit if not give notices.
		if ( ! $el.hasClass( 'give-notice' ) ) {
			return;
		}

		event.preventDefault();
		$el.fadeTo( 100, 0, function() {
			$el.slideUp( 100, function() {
				$el.remove();
			} );
		} );
	} );
} );
