/* globals jQuery, Give */
jQuery( document ).ready( function() {
	const $addonLink = jQuery( '#menu-posts-give_forms a[href^="https://givewp.com"]' );

	$addonLink.attr( 'target', '_blank' );

	if ( parseInt( Give.fn.getGlobalVar( 'highlightAddonLink' ) ) ) {
		$addonLink.addClass( 'give-highlight' );
		$addonLink.prepend( '<span class="dashicons dashicons-star-filled"></span>' );
	}
} );
