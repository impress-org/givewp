/* globals jQuery */
jQuery( document ).ready( function() {
	const $addonLink = jQuery( '#menu-posts-give_forms a[href^="https://givewp.com"]' );

	$addonLink.attr( 'target', '_blank' );
} );
